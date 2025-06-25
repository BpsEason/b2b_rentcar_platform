<template>
  <div class="vehicle-list">
    <h1>可用車輛</h1>
    <div v-if="loading">載入中...</div>
    <div v-else-if="error">{{ error }}</div>
    <div v-else class="vehicles-grid">
      <div v-for="vehicle in vehicles" :key="vehicle.id" class="vehicle-card">
        <h3>{{ vehicle.make }} {{ vehicle.model }} ({{ vehicle.year }})</h3>
        <p>車牌: {{ vehicle.license_plate }}</p>
        <p>每日費率: TWD {{ vehicle.daily_rate }}</p>
        <p v-if="vehicle.dynamic_price">動態價格: TWD {{ vehicle.dynamic_price }}</p> # 顯示動態價格
        <p>狀態: {{ vehicle.status }}</p>
        <p>據點: {{ vehicle.location ? vehicle.location.name : '未知' }}</p>
        <button @click="viewVehicleDetail(vehicle.id)">查看詳情</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const vehicles = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
  try {
    const response = await axios.get('/vehicles');
    vehicles.value = response.data;
  } catch (err) {
    error.value = '無法載入車輛列表。';
    console.error('Error fetching vehicles:', err);
  } finally {
    loading.value = false;
  }
});

const viewVehicleDetail = (id) => {
  // TODO: 導航到車輛詳情頁面，這裡應替換為一個模態框或新頁面，而不是 alert
  // alert(`查看車輛 ${id} 的詳情`);
  console.log(`查看車輛 ${id} 的詳情`);
};
</script>

<style scoped>
.vehicle-list {
  padding: 20px;
}
.vehicles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  margin-top: 20px;
}
.vehicle-card {
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 15px;
  text-align: left;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.vehicle-card h3 {
  color: #333;
  margin-top: 0;
}
.vehicle-card p {
  color: #666;
  font-size: 0.9em;
  margin-bottom: 5px;
}
.vehicle-card button {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  margin-top: 10px;
}
.vehicle-card button:hover {
  background-color: #0056b3;
}
</style>
