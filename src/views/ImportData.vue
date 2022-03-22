<style scoped>
.modalBackground {
  position: absolute;
  width: 100vw;
  height: 100vh;
  top: 0;
  left: 0;
  background-color: #00000088;
}

.modal {
  position: relative;
  background-color: #ffffff;
  margin: 0 auto;
  margin-top: 20px;
  max-width: 900px;
}
</style>

<template>
  <div v-if="exibirModal">
    <div class="modalBackground">
      <div class="modal">
        <div class="flex-column alignitens-start pv5 ph10">
          <div>
            <div class="pageTitle">Import Backup</div>
          </div>
          <div>
              Import File
              <input type="file" name="importfile" id="importfile" ref="importfile">
          </div>
          <!-- <div>
              <input class="form-input" type="text" v-model="contaLocal.nome">
          </div> -->
          <div class="flex">
            <button class="btn form-input" @click="fecharModal()">Fechar</button>
            <button class="btn form-input" @click="importFile()">Salvar</button>
          </div>
        </div>
      </div>
    </div>
    <Loader :busy="busy"></Loader>
  </div>
</template>

<script>
import EventBus from '@/core/EventBus.js';
import notify from '@/core/notify.js';
import deepCopy from '@/core/deepcopy.js';
import Loader from '@/components/Loader.vue';

export default {
  name: "ImportData",
  components: {
    Loader
  },
  data () {
    return {
      exibirModal: false,
      busy: false,
      fileToImport: null,
      editadoComSucesso: false,
    };
  },
  // props: ["modalActive"],
  props: {
    exibirModalImport: Boolean,
  },
  methods: {
    fecharModal() {
      this.exibirModal = false;
      this.$emit('update:exibirModalImport', this.exibirModal)
      if(this.editadoComSucesso == true) {
        EventBus.$emit('LISTACONTAS_INDEX', {});
      }
    },
    importFile() {
      this.busy = true;

      let dataForm = new FormData();

      for (let file of this.$refs.importfile.files) {
        console.log('[file]',file);
        dataForm.append(`file`, file);
      }

      console.log('[dataForm]',dataForm);

      let headers = new Headers();

      fetch('http://localhost:8000' + "/backup/import", {
        headers: headers,
        method: 'POST',
        body: dataForm,
      }).then(async (response) => {
        console.log('[response]',response);
        let responseText = await response.text();
        console.log('[responseText]',responseText);
        let responseData = JSON.parse(responseText);
        console.log('[responseData]',responseData);
        this.busy = false;
        this.editadoComSucesso = true;
        notify.notify(responseData.message, "success");
        this.fecharModal();
      }).catch(error => {
          this.busy = false;
          notify.notify(error, "error");
      })
    },
  },
  watch: {
    exibirModalImport(newProp, oldProp) {
      this.exibirModal = newProp;
    },
  }
}
</script>
