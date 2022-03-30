<template>
  <div>
    <div v-if="busy" class="loaderContainer">
      <div class="loader"></div>
    </div>
  </div>
</template>

<script>
export default {
  data: function () {
    return {
      ini: ''
    }
  },
  props: {
    busy: Boolean
  }
}
</script>

<style>
.loaderContainer {
  position:absolute;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: #0000008f;
  display: flex;
  justify-content: center;
  align-items: center;
}

.loader {
  border: 10px solid #f3f3f3; /* Light grey */
  border-top: 10px solid #3498db; /* Blue */
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 2s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
