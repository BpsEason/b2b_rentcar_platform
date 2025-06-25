<template>
  <div class="booking-list">
    <h1>我的訂單</h1>
    <div v-if="loading">載入中...</div>
    <div v-else-if="error">{{ error }}</div>
    <div v-else-if="bookings.length === 0">您目前沒有任何訂單。</div>
    <div v-else class="bookings-grid">
      <div v-for="booking in bookings" :key="booking.id" class="booking-card">
        <h3>訂單編號: {{ booking.id }}</h3>
        <p>車輛: {{ booking.vehicle.make }} {{ booking.vehicle.model }}</p>
        <p>取車據點: {{ booking.pickup_location.name }}</p>
        <p>還車據點: {{ booking.return_location.name }}</p>
        <p>取車時間: {{ new Date(booking.pickup_datetime).toLocaleString() }}</p>
        <p>還車時間: {{ new Date(booking.return_datetime).toLocaleString() }}</p>
        <p>總金額: TWD {{ booking.total_amount }}</p>
        <p>狀態: <span :class="['status', booking.status]">{{ booking.status }}</span></p>
        <p>付款狀態: <span :class="['status', booking.payment_status]">{{ booking.payment_status }}</span></p>
        <button v-if="booking.status === 'pending' || booking.status === 'confirmed'" 
                @click="cancelBooking(booking.id)" :disabled="isCancelling === booking.id">
            {{ isCancelling === booking.id ? '取消中...' : '取消訂單' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const bookings = ref([]);
const loading = ref(true);
const error = ref(null);
const isCancelling = ref(null); // 追蹤正在取消的訂單ID

onMounted(async () => {
  await fetchBookings();
});

const fetchBookings = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get('/user/bookings');
    bookings.value = response.data;
  } catch (err) {
    error.value = '無法載入訂單列表。請確認您已登入。';
    console.error('Error fetching bookings:', err);
  } finally {
    loading.value = false;
  }
};

const cancelBooking = async (bookingId) => {
  if (!confirm('您確定要取消此訂單嗎？')) { # TODO: 替換為自定義模態框，而不是 alert
    return;
  }
  isCancelling.value = bookingId;
  try {
    await axios.post(`/bookings/${bookingId}/cancel`);
    alert('訂單已成功取消！'); # TODO: 替換為自定義消息提示
    await fetchBookings(); // 重新載入訂單列表以更新狀態
  } catch (err) {
    error.value = err.response?.data?.message || '取消訂單失敗。';
    console.error('Error cancelling booking:', err);
  } finally {
    isCancelling.value = null;
  }
};
</script>

<style scoped>
.booking-list {
  padding: 20px;
}
.bookings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}
.booking-card {
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 15px;
  text-align: left;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.booking-card h3 {
  color: #333;
  margin-top: 0;
}
.booking-card p {
  color: #666;
  font-size: 0.9em;
  margin-bottom: 5px;
}
.status {
  font-weight: bold;
  padding: 3px 8px;
  border-radius: 4px;
}
.status.pending { background-color: #ffc107; color: #333; }
.status.confirmed { background-color: #28a745; color: white; }
.status.rented { background-color: #17a2b8; color: white; }
.status.returned { background-color: #6c757d; color: white; }
.status.cancelled { background-color: #dc3545; color: white; }
</style>
