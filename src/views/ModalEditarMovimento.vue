<style>
</style>

<template>
  <div v-if="exibirModal">
    <div class="modalBackground">
      <div class="page">
        <div class="whitebox flex-column alignitens-start">
          <div>
            <div class="pageTitle">Editar Movimento</div>
          </div>
          <div>
            <input class="form-input" type="text" v-model="movimentoLocal.descricao">
          </div>
          <div>
            <input class="form-input" type="text" v-model="movimentoLocal.nomeLoja">
          </div>
          <div>
            <input class="form-input" type="text" v-model="movimentoLocal.valor">
          </div>
          <div>
            <input class="form-input" type="date" v-model="movimentoLocal.data">
          </div>
          <span class="mt10">Lista de Itens do Movimento</span>
          
          <div class="whitebox mt10 flex justify-spacebetween alignitens-start" v-for="item in movimentoLocal.itensMovimentos" :key="item.id">
            <div>
              {{item.nome}} - R$ {{item.valor}}
              <input name="nome" class="form-input form-input-sm font-size-small" type="text" placeholder="nome" v-model="item.nome">
              <input name="valor" class="form-input form-input-sm font-size-small" type="money" placeholder="valor" v-model="item.valor">
            </div>
            <div>
              <button class="btn font-size-small" @click="retirarItemMovimento(item.id)"><i class="fas fa-times"></i> </button>
            </div>
          </div>
          <span>
            <button class="form-input btn btn-sm" @click="adicionarItemMovimento()">Adicionar Item</button>
          </span>

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
      console.log('[LOG]',this.movimentoLocal.itensMovimentos);
      this.busy = true;
      let url = 'http://localhost:8000/movimentos/' + this.movimentoLocal.id;
      console.log(this.movimentoLocal);
      let body = {
        'descricao': this.movimentoLocal.descricao,
        'nomeLoja': this.movimentoLocal.nomeLoja,
        'data': this.movimentoLocal.data,
        // 'valor': this.movimentoLocal.valor,
        'itensMovimentos': this.movimentoLocal.itensMovimentos
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

