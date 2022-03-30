<style>
</style>

<template>
  <div>
    <div class="page">

      <div class="box">
        <div class="pageTitle">Contas</div>
      </div>

      <div class="whitebox mt5">
        <div class="pageSubtitle">Criar Conta</div>
        <input name="nome" class="form-input"
          type="text" placeholder="nome" 
          v-model="novaNotaNome">
        <button class="btn" @click="criarconta()">Criar conta</button>
      </div>
    
      <div class="box mt10">
        <div class="pageSubtitle">Lista de Contas</div>
      </div>

      <div v-for="conta in contas" :key="conta.id" class="whitebox mt10 flex-column justify-spacebetween"><!-- flex justify-spacebetween alignitens-center">-->
        <span class="mv5">
          {{ conta.nome }} [R$ {{ conta.saldo }}]
        </span>
        <span class="mv5">
          <router-link v-bind:to="'/listaMovimentos/'+conta.id" class="btn btn-sm"><i class="fas fa-arrow-right"></i> Acessar</router-link>
        <!-- </span>
        <span class="mv5"> -->
          <button @click="ativarModalEdicao(conta.id)" class="btn btn-sm ml5"><i class="fas fa-edit"></i> Editar</button>
        <!-- </span>
        <span class="mv5"> -->
          <button @click="ativarModalExcluirConta(conta.id)" class="btn btn-sm ml5"><i class="fas fa-trash"></i> Excluir</button>
        </span>
      </div>
    </div>

    <Loader :busy="busy"></Loader>

    <ModalEditarConta :exibirModalEdicao.sync="exibirModalEdicao" :conta="contaEditar"></ModalEditarConta>
    <ModalExcluirConta :exibirModalExcluirConta.sync="exibirModalExcluirConta" :conta="contaExcluir"></ModalExcluirConta>
  </div>
</template>

<script>
import EventBus from '@/core/EventBus.js';
import notify from '@/core/notify.js';
import Loader from '@/components/Loader.vue';
import ModalEditarConta from '@/views/ModalEditarConta.vue';
import ModalExcluirConta from '@/views/ModalExcluirConta.vue';

export default {
  name: 'ListaContas',
  components: {
    Loader,
    ModalEditarConta,
    ModalExcluirConta
  },
  data: () => {
    return {
      busy: false,
      novaNotaNome: '',
      contas: [],
      // noticeboxQueue: [],
      exibirModalEdicao: false,
      exibirModalExcluirConta: false,
      contaEditar: {},
      contaExcluir: {}
    }
  },
  methods: {
    ativarModalExcluirConta (contaId) {
      let contaEncontrada = this.contas.filter((conta) => {
        return conta.id == contaId;
      });
      contaEncontrada = contaEncontrada[0]
      this.contaExcluir = contaEncontrada
      this.exibirModalExcluirConta = true;
    },
    ativarModalEdicao (contaId) {
      let contaEncontrada = this.contas.filter((conta) => {
        return conta.id == contaId;
      });
      contaEncontrada = contaEncontrada[0]
      this.contaEditar = contaEncontrada
      this.exibirModalEdicao = true;
    },
    buscaContas () {
      this.busy = true;
      let url = 'http://localhost:8000/contas';
      let data = {
        method: 'get'
      };
      fetch(url,data)
      .then(async response => {
        data = await response.json();
        console.log('[LOG]',response);
        console.log('[LOG]',data);
        this.contas = data.contas
        this.busy = false;
        notify.notify('carregado!', "success");
      })
    },
    criarconta() {
      this.busy = true;
      let url = 'http://localhost:8000/contas';
      let body = {
        'nome': this.novaNotaNome
      };
      let data = {
        method: 'post',
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
        notify.notify('criado!', "success");
        this.buscaContas();
      })
      .catch(error => {
        console.log('[LOG]',error);
      });
    },
    // excluirConta(idConta){
    //   this.busy = true;
    //   let url = 'http://localhost:8000/contas/' + idConta;
    //   let data = {
    //     method: 'delete',
    //   };
    //   fetch(url,data)
    //   .then(async response => {
    //     data = await response.json();
    //     console.log('[LOG]',response);
    //     console.log('[LOG]',data);
    //     if(!response.ok){
    //       this.busy = false;
    //       notify.notify(data.message, "error");
    //       return;
    //     }
    //     this.busy = false;
    //     notify.notify('deletado!', "success");
    //     this.buscaContas();
    //   })
    //   .catch(error => {
    //     console.log('[LOG]',error);
    //   });
    // },
  },
  watch: {
  },
  created () {
    this.buscaContas();
    EventBus.$on('LISTACONTAS_INDEX', (data) => {
      this.buscaContas();
    });
  },
  destroyed() {
    EventBus.$off('LISTACONTAS_INDEX');
  }
}
</script>
