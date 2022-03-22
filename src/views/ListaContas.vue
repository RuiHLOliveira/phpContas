<style>
@import url('https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap');
* {
  font-family: 'Ubuntu', sans-serif;
  color: #000000;
}
.pageTitle {
  font-size: 1.5rem;
  margin: 20px 0px 10px 0px;
}
.pageSubtitle {
  font-size: 1.1rem;
  margin: 20px 0px 10px 0px;
}
.form-input {
  margin: 10px 0px;
  padding: 10px;
  display: block;
}

.form-input.btn {
  margin: 10px 0px;
  padding: 10px;
  display: block;
}

.btn {
  text-decoration: none;
  border: 0px;
  margin: 5px 5px;
  padding: 5px 10px;
  color: inherit;
}
.btn:hover {
  background: #ffffff77;
}

.btn-lightgray {
  border: 0px;
  margin: 5px 5px;
  padding: 5px 10px;
  background: #eaeaea;
}
.btn-lightgray:hover {
  background: #dadada;
}

.conta {
  border-radius: 3px;
  background: #81b6e2;
  margin: 10px 0px;
  padding: 5px 10px;
  color: #ffffff;
}

.flex {
  display: flex;
}

.flex-column {
  display: flex;
  flex-direction: column;
}

.justify-spacebetween {
  justify-content: space-between;
}

.alignitens-start {
  align-items: flex-start;
}
.alignitens-center {
  align-items: center;
}



.p5 {
  padding: 5px;
}
.pv5 {
  padding-top: 5px;
  padding-bottom: 5px;
}
.ph5 {
  padding-right: 5px;
  padding-left: 5px;
}
.pt5{
  padding-top: 5px;
}
.pr5{
  padding-right: 5px;
}
.pb5{
  padding-bottom: 5px;
}
.pl5{
  padding-left: 5px;
}

.p10 {
  padding: 10px;
}
.pv10 {
  padding-top: 10px;
  padding-bottom: 10px;
}
.ph10 {
  padding-right: 10px;
  padding-left: 10px;
}
.pt10{
  padding-top: 10px;
}
.pr10{
  padding-right: 10px;
}
.pb10{
  padding-bottom: 10px;
}
.pl10{
  padding-left: 10px;
}

</style>

<template>
  <div>
    <div class="pageTitle">Contas</div>

    <div class="pageSubtitle">Criar Conta</div>
    
    <input name="nome" class="form-input"
      type="text" placeholder="nome" 
      v-model="novaNotaNome">
    <button class="btn form-input" @click="criarconta()">Criar conta</button>

    <div class="pageSubtitle">Contas</div>

    <div v-for="conta in contas" :key="conta.id" class="conta flex justify-spacebetween alignitens-center">
      <span>
        <router-link v-bind:to="'/listaMovimentos/'+conta.id" class="btn">{{ conta.nome }}</router-link>
      </span>
      <span>
        {{ conta.saldo }}
        <button @click="ativarModalEdicao(conta.id)" class="btn">editar</button>
        <button @click="ativarModalExcluirConta(conta.id)" class="btn">excluir</button>
      </span>
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
    excluirConta(idConta){
      this.busy = true;
      let url = 'http://localhost:8000/contas/' + idConta;
      let data = {
        method: 'delete',
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
        notify.notify('deletado!', "success");
        this.buscaContas();
      })
      .catch(error => {
        console.log('[LOG]',error);
      });
    },
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
