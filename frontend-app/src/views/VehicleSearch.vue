<template>
  <div class="vehicle-search">
    <h1>搜尋車輛</h1>
    <form @submit.prevent="searchVehicles">
      <div class="form-group">
        <label for="pickup_location">取車地點:</label>
        <select v-model="form.pickup_location_id" id="pickup_location" required>
          <option v-for="location in locations" :key="location.id" :value="location.id">
            {{ location.name }} ({{ location.address }}, {{ location.city }})
          </option>
        </select>
      </div>
      <div class="form-group">
        <label for="return_location">還車地點:</label>
        <select v-model="form.return_location_id" id="return_location" required>
          <option v-for="location in locations" :key="location.id" :value="location.id">
            {{ location.name }} ({{ location.address }}, {{ location.city }})
          </option>
        </select>
      </div>
      <div class="form-group">
        <label for="pickup_datetime">取車時間:</label>
        <input type="datetime-local" v-model="form.pickup_datetime" id="pickup_datetime" required>
      </div>
      <div class="form-group">
        <label for="return_datetime">還車時間:</label>
        <input type="datetime-local" v-model="form.return_datetime" id="return_datetime" required>
      </div>
      <button type="submit" :disabled="loading">
        {{ loading ? '搜尋中...' : '搜尋' }}
      </button>
      <p v-if="error" class="error-message">{{ error }}</p>
    </form>

    <div v-if="vehicles.length > 0" class="vehicles-grid">
        <h2>搜尋結果</h2>
        <div v-for="vehicle in vehicles" :key="vehicle.id" class="vehicle-card">
            <h3>{{ vehicle.make }} {{ vehicle.model }} ({{ vehicle.year }})</h3>
            <p>車牌: {{ vehicle.license_plate }}</p>
            <p>原始費率: TWD {{ vehicle.daily_rate }}</p>
            <p v-if="vehicle.dynamic_price">動態價格: <span class="highlight-price">TWD {{ vehicle.dynamic_price }}</span></p>
            <p>狀態: {{ vehicle.status }}</p>
            <p>取車據點: {{ vehicle.location ? vehicle.location.name : '未知' }}</p>
            <button @click="selectVehicle(vehicle.id)">選擇此車</button>
        </div>
    </div>
    <div v-else-if="!loading && !error && searchAttempted" class="no-results">
        <p>沒有找到符合條件的車輛。</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const form = ref({
  pickup_location_id: null,
  return_location_id: null,
  pickup_datetime: '',
  return_datetime: '',
});
const locations = ref([]);
const vehicles = ref([]);
const loading = ref(false);
const error = ref(null);
const searchAttempted = ref(false);

onMounted(async () => {
  try {
    const response = await axios.get('/locations');
    locations.value = response.data;
    if (locations.value.length > 0) {
      form.value.pickup_location_id = locations.value[0].id;
      form.value.return_location_id = locations.value[0].id;
    }
  } catch (err) {
    error.value = '無法載入地點列表。';
    console.error('Error fetching locations:', err);
  }
});

const searchVehicles = async () => {
  loading.value = true;
  error.value = null;
  vehicles.value = [];
  searchAttempted.value = true;

  try {
    const response = await axios.get('/vehicles/search', { params: form.value });
    vehicles.value = response.data;
  } catch (err) {
    error.value = err.response?.data?.message || '搜尋失敗，請檢查輸入或稍後再試。';
    console.error('Error searching vehicles:', err.response?.data || err.message);
  } finally {
    loading.value = false;
  }
};

const selectVehicle = (vehicleId) => {
  // TODO: 導航到預訂頁面，並帶上選中的車輛資訊
  console.log(`選擇了車輛 ID: ${vehicleId}，準備進行預訂。`);
  // 可以在這裡跳轉到預訂表單頁面，並預填車輛ID和租賃時間等資訊
};
</script>

<style scoped>
.vehicle-search {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
}
.form-group {
  margin-bottom: 15px;
  text-align: left;
}
.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}
.form-group input[type="datetime-local"],
.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"],
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 1em;
  box-sizing: border-box;
}
button[type="submit"] {
  width: 100%;
  padding: 12px;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 5px;
  font-size: 1.1em;
  cursor: pointer;
  transition: background-color 0.3s ease;
}
button[type="submit"]:hover {
  background-color: #218838;
}
button[type="submit"]:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.vehicles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  margin-top: 30px;
  border-top: 1px solid #eee;
  padding-top: 20px;
}
.vehicles-grid h2 {
    text-align: center;
    grid-column: 1 / -1; /* 讓標題居中 */
    margin-bottom: 20px;
}
.vehicle-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 15px;
  text-align: left;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  background-color: #fefefe;
}
.vehicle-card h3 {
  color: #333;
  margin-top: 0;
  margin-bottom: 10px;
}
.vehicle-card p {
  color: #666;
  font-size: 0.9em;
  margin-bottom: 5px;
}
.highlight-price {
    color: #dc3545;
    font-weight: bold;
    font-size: 1.1em;
}
.vehicle-card button {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  margin-top: 10px;
  width: auto; /* 讓按鈕寬度自適應 */
}
.vehicle-card button:hover {
  background-color: #0056b3;
}
.no-results {
    margin-top: 20px;
    padding: 15px;
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    border-radius: 5px;
}
</style>
