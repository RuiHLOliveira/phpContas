<template>
  <div id="app">
    <div id="nav">
      <router-link to="/">Home</router-link> |
      <router-link to="/listaContas">ListaContas</router-link>
      <a href="#" class="btn mr-1" @click="exportData()">Export Data</a>
      <a href="#" class="btn mr-1" @click="openImportDataModal()">Import Data</a>
    </div>
    <router-view/>
    <ImportData :exibirModalImport.sync="exibirModalImport" />
    <Loader :busy="busy"></Loader>
    <NoticeBox></NoticeBox>
  </div>
</template>

<style>

</style>

<script>

import NoticeBox from '@/components/NoticeBox.vue';
import EventBus from '@/core/EventBus.js';
import notify from '@/core/notify.js';
import Loader from '@/components/Loader.vue';
import ImportData from "@/views/ImportData.vue";

export default {
  name: "App",
  components: {
    ImportData,
    NoticeBox,
    Loader
  },
  data: function () {
    return {
      busy: false,
      exibirModalImport: false,
    }
  },
  methods: {
    openImportDataModal() {
      this.exibirModalImport = true;
    },
    exportData() {
      this.busy = true;
      let requestData = {
        method: 'GET',
      };
      let url = 'http://localhost:8000' + "/backup/export";
      fetch(url, requestData)
      .then(async response => {
        console.log('[LOG]',response);
        let data = await response.json();
        console.log('[LOG]',data);
        notify.notify('Arquivo Exportado para download', "success");
        const blob = new Blob([JSON.stringify(data)], { type: 'application/json' })
        const link = document.createElement('a')
        link.href = URL.createObjectURL(blob)
        let date = new Date();
        date = date.toISOString().substr(0, 19);
        date = date.replaceAll( ':','.');
        console.log('date',date);
        link.download = date + '.phpcontas.export.json'
        link.click()
        URL.revokeObjectURL(link.href)
        this.busy = false;
      })
      .catch((error) => {
        this.busy = false;
        console.error(error);
        notify.notify(error, "error");
      });
    },
  }
};
</script>
