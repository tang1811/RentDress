<script setup>
import Navbar from './components/Navbar.vue'
</script>

<template>
  <div id="app">
    <Navbar />
    <main>
      <router-view />
    </main>
    <footer class="footer mt-5">
      <div class="container">
        <div class="row">
          <div class="col-md-4 mb-4">
            <h5 class="text-warning">RentDress</h5>
            <p class="text-white">บริการให้เช่าชุดราตรี ชุดไทย สูท และชุดพิเศษ คุณภาพพรีเมียม</p>
          </div>
          <div class="col-md-4 mb-4">
            <h6 class="text-warning">ติดต่อเรา</h6>
            <p class="text-white mb-1">โทร: 02-XXX-XXXX</p>
            <p class="text-white mb-1">Line: @rentdress</p>
            <p class="text-white">Email: contact@rentdress.com</p>
          </div>
          <div class="col-md-4 mb-4">
            <h6 class="text-warning">เวลาทำการ</h6>
            <p class="text-white mb-1">จันทร์ - เสาร์: 10:00 - 20:00</p>
            <p class="text-white">อาทิตย์: 11:00 - 18:00</p>
          </div>
        </div>
        <hr class="bg-secondary">
        <p class="text-center text-white mb-0">&copy; 2024 RentDress. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<style>
#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

main {
  flex: 1;
}
</style>
