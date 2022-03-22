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
            <div class="pageTitle">Editar Movimento</div>
          </div>
          <div>
            <input class="form-input" type="text" v-model="movimentoLocal.descricao">
          </div>
          <div>
            <input class="form-input" type="text" v-model="movimentoLocal.valor">
          </div>
          <div>
            <input class="form-input" type="date" v-model="movimentoLocal.data">
          </div>
          <div class="flex">
            <button class="btn form-input" @click="fecharModal()">Fechar</button>
            <button class="btn form-input" @click="editarMovimento()">Salvar</button>
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
      movimentoLocal: {},
      busy: false,
      editadoComSucesso: false
    }
  },
  props: {
    exibirModalEdicao: Boolean,
    movimento: Object
  },
  methods: {
    fecharModal() {
      this.exibirModal = false;
      this.$emit('update:exibirModalEdicao', this.exibirModal)
      if(this.editadoComSucesso == true) EventBus.$emit('LISTAMOVIMENTOS_INDEX', {});
    },
    editarMovimento() {
      this.busy = true;
      let url = 'http://localhost:8000/movimentos/' + this.movimentoLocal.id;
      console.log(this.movimentoLocal);
      let body = {
        'descricao': this.movimentoLocal.descricao,
        'data': this.movimentoLocal.data,
        'valor': this.movimentoLocal.valor,
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
    movimento(newProp, oldProp) {
      this.movimentoLocal = deepCopy.deepCopy(newProp);
      // this.movimentoLocal.data = this.movimentoLocal.data.date.substr(0, 10);
      // this.movimentoLocal.valor = this.movimentoLocal.valor.replace('.',',');
      console.log('movimentolocal',this.movimentoLocal.data);
    }
  }
}
</script>

