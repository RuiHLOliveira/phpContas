<style>
</style>

<template>
  <div v-if="exibirModal">
    <div class="modalBackground">
      <div class="page">
        <div class="whitebox flex-column alignitens-start">
          <div>
            <div class="pageTitle">Editar Conta</div>
          </div>
          <div>
            <input class="form-input" type="text" v-model="contaLocal.nome">
          </div>
          <div class="flex">
            <button class="btn form-input" @click="fecharModal()">Fechar</button>
            <button class="btn form-input" @click="editarConta()">Salvar</button>
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
  components: {
    Loader
  },
  data: function () {
    return {
      exibirModal: false,
      contaLocal: {},
      busy: false,
      editadoComSucesso: false
    }
  },
  props: {
    exibirModalEdicao: Boolean,
    conta: Object
  },
  methods: {
    fecharModal() {
      this.exibirModal = false;
      this.$emit('update:exibirModalEdicao', this.exibirModal)
      if(this.editadoComSucesso == true) {
        EventBus.$emit('LISTACONTAS_INDEX', {});
      }
    },
    editarConta() {
      this.busy = true;
      let url = 'http://localhost:8000/contas/' + this.contaLocal.id;
      let body = {
        'nome': this.contaLocal.nome
      };
      let data = {
        method: 'PUT',
        body: JSON.stringify(body)
      };
      fetch(url,data)
      .then(async response => {
        data = await response.json();
        console.log('[LOG]',response);
        console.log('[LOG]',data);
        if(!response.ok){
          this.busy = false;
          notify.notify(data.message, "error");
          return;
        }
        this.busy = false;
        notify.notify('Editado!', "success");
        this.editadoComSucesso = true;
      })
      .catch(error => {
        console.log('[LOG]',error);
      });
    },
  },
  watch: {
    exibirModalEdicao(newProp, oldProp) {
      this.exibirModal = newProp;
    },
    conta(newProp, oldProp) {
      this.contaLocal = deepCopy.deepCopy(newProp);
    }
  }
}
</script>

