<style>
</style>

<template>
  <div v-if="exibirModal">
    <div class="modalBackground">
      <div class="page">
        <div class="whitebox flex-column alignitens-start">
          <div>
            <div class="pageTitle">Excluir Conta</div>
          </div>
          <div>
            Tem certeza que deseja excluir a conta {{contaLocal.nome}} e todos os movimentos?
          </div>
          <div class="flex">
            <button class="btn form-input" @click="fecharModal()">Fechar</button>
            <button v-if="!excluidoComSucesso" class="btn form-input" @click="editarConta()">Excluir</button>
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
      excluidoComSucesso: false
    }
  },
  props: {
    exibirModalExcluirConta: Boolean,
    conta: Object
  },
  methods: {
    fecharModal() {
      this.exibirModal = false;
      this.$emit('update:exibirModalExcluirConta', this.exibirModal)
      if(this.excluidoComSucesso == true){
        this.excluidoComSucesso = false;
        EventBus.$emit('LISTACONTAS_INDEX', {});
      }
    },
    editarConta() {
      this.busy = true;
      let url = 'http://localhost:8000/contas/' + this.contaLocal.id;
      let data = {
        method: 'DELETE',
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
        notify.notify('Excluido!', "success");
        this.excluidoComSucesso = true;
      })
      .catch(error => {
        console.log('[LOG]',error);
      });
    },
  },
  watch: {
    exibirModalExcluirConta(newProp, oldProp) {
      this.exibirModal = newProp;
    },
    conta(newProp, oldProp) {
      this.contaLocal = deepCopy.deepCopy(newProp);
    }
  }
}
</script>

