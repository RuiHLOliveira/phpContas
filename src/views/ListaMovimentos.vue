<style>
@import url('https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap');

table,
table tr,
table tr td,
table tr th {
  border: 0px solid black;
  border-collapse: collapse;
  text-align: left;
}

.fixed {
  position: fixed;
}

.listaMovimentos {
  width: 100%;
}
.listaMovimentos tr {
  border-bottom: 1px solid #dddddd;
}
.listaMovimentos td,
.listaMovimentos th {
  padding: 5px 10px;
}

.align-right {
  text-align: right;
}

</style>

<template>
  <div>
    <div>

      <div class="pageTitle">Movimentos</div>

      <div class="pageSubtitle">Criar Movimento</div>
      
      <div class="flex-column">

        <input name="descricao" class="form-input" type="text" placeholder="descricao" v-model="novoMovimentoDescricao">
          
        <input name="valor" class="form-input" type="money" placeholder="valor" v-model="novoMovimentoValor">
          
        <input name="data" class="form-input" type="date" placeholder="data" v-model="novoMovimentoData">

        <button class="form-input btn" @click="criarMovimento()">Criar movimento</button>

      </div>


      <div class="pageSubtitle">Dados da conta</div>
      <span>{{conta.nome}} - R$ {{conta.saldo}}</span>

    </div>

    <div class="pageSubtitle">Movimentos</div>
    <table class="listaMovimentos">
      <tr>
        <th>descricao</th>
        <th class="align-right">valor</th>
        <th class="align-right">data</th>
        <th class="align-right">acoes</th>
      </tr>
      <tr v-for="movimento in movimentos" :key="movimento.id">
        <td>{{ movimento.descricao }}</td>
        <td class="align-right">{{ movimento.valor }}</td>
        <td class="align-right"><!--{{ movimento.data }} - -->{{ movimento.dataExibicao }}</td>
        <td class="align-right">
          <button class="btn" @click="ativarModalEdicao(movimento.id)"><i class="fas fa-edit"></i></button>
          <button class="btn" @click="excluirMovimento(movimento.id)"><i class="fas fa-trash-alt"></i></button>
          <!-- <button @click="ativarModalExcluirConta(conta.id)" class="btn">excluir</button> -->
        </td>
      </tr>
    </table>
    
    <Loader :busy="busy"></Loader>

    <ModalEditarMovimento :exibirModalEdicao.sync="exibirModalEdicao" :movimento="movimentoEditar"></ModalEditarMovimento>
    <!-- <ModalExcluirConta :exibirModalExcluirConta.sync="exibirModalExcluirConta" :conta="contaExcluir"></ModalExcluirConta> -->
  </div>
</template>

<script>
import EventBus from '@/core/EventBus.js';
import notify from '@/core/notify.js';
import Loader from '@/components/Loader.vue';
import ModalEditarMovimento from '@/views/ModalEditarMovimento.vue';
// import ModalEditarConta from '@/views/ModalEditarConta.vue';
// import ModalExcluirConta from '@/views/ModalExcluirConta.vue';

export default {
  name: 'ListaContas',
  components: {
    Loader,
    ModalEditarMovimento,
    // ModalExcluirConta
  },
  props: {
    idConta: String
  },
  data: () => {
    return {
      busy: false,
      conta: {},
      novoMovimentoDescricao: '',
      novoMovimentoValor: '',
      novoMovimentoData: '',
      buscarMovimentoOrdemDecrescente: true,
      movimentos: [],
      // noticeboxQueue: [],
      exibirModalEdicao: false,
      // exibirModalExcluirConta: false,
      movimentoEditar: {},
      // contaExcluir: {}
    }
  },
  methods: {
    // ativarModalExcluirConta (contaId) {
    //   let contaEncontrada = this.contas.filter((conta) => {
    //     return conta.id == contaId;
    //   });
    //   contaEncontrada = contaEncontrada[0]
    //   this.contaExcluir = contaEncontrada
    //   this.exibirModalExcluirConta = true;
    // },
    // ativarModalEdicao (contaId) {
    //   let contaEncontrada = this.contas.filter((conta) => {
    //     return conta.id == contaId;
    //   });
    //   contaEncontrada = contaEncontrada[0]
    //   this.contaEditar = contaEncontrada
    //   this.exibirModalEdicao = true;
    // },
    buscaConta(){
      this.busy = true;
      let url = 'http://localhost:8000/contas/' + this.idConta;
      let data = {
        method: 'get'
      };
      fetch(url,data)
      .then(async response => {
        data = await response.json();
        console.log('[LOG]',response);
        console.log('[LOG]',data);
        this.conta = data.conta
        this.busy = false;
        notify.notify('carregado!', "success");
      })
    },
    ativarModalEdicao (movimentoId) {
      let movimentoEncontrado = this.movimentos.filter((movimento) => {
        return movimento.id == movimentoId;
      });
      movimentoEncontrado = movimentoEncontrado[0]
      this.movimentoEditar = movimentoEncontrado
      this.exibirModalEdicao = true;
    },
    buscaMovimentos () {
      this.busy = true;
      let url = 'http://localhost:8000/movimentos?idConta='+this.idConta;
      if(this.buscarMovimentoOrdemDecrescente == true) {
        url += `&data=desc`
      }
      let data = {
        method: 'get'
      };
      fetch(url,data)
      .then(async response => {
        data = await response.json();
        console.log('[LOG]',response);
        console.log('[LOG]',data);
        this.movimentos = this.processaMovimentos(data.movimentos);
        this.busy = false;
        notify.notify('carregado!', "success");
      })
    },
    processaMovimentos(movimentos){
      movimentos.forEach(movimento => {
        let data = new Date(movimento.data.date);
        let dia = String(data.getDate()).padStart(2,'0');
        let mes = String(data.getMonth()+1).padStart(2,'0');
        let ano = data.getFullYear()
        movimento.dataExibicao =  `${dia}/${mes}/${ano}`
        movimento.valor = movimento.valor.replace('.',',');
        
        movimento.data = movimento.data.date.substr(0, 10);
      });
      return movimentos;
    },
    criarMovimento() {
      this.busy = true;
      let url = 'http://localhost:8000/movimentos';
      let body = {
        'descricao': this.novoMovimentoDescricao,
        'valor': this.novoMovimentoValor,
        'data': this.novoMovimentoData,
        'idConta' : this.idConta
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
        this.setDataDefault();
        this.buscaConta();
        this.buscaMovimentos();
      })
      .catch(error => {
        console.log('[LOG]',error);
      });
    },
    excluirMovimento(idMovimento){
      if(!window.confirm('Deseja excluir o movimento?')){
        return;
      }
      this.busy = true;
      let url = 'http://localhost:8000/movimentos/' + idMovimento;
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
        this.buscaConta();
        this.buscaMovimentos();
      })
      .catch(error => {
        console.log('[LOG]',error);
      });
    },
    setDataDefault () {
      let date = new Date();
      date = date.toISOString().substr(0, 10);
      // date = '2021-12-01';
      this.novoMovimentoData = date;
      this.novoMovimentoDescricao = null;
      this.novoMovimentoValor = null;
    }
  },
  watch: {
  },
  created () {
    this.setDataDefault();
    this.buscaConta();
    this.buscaMovimentos();
    EventBus.$on('LISTAMOVIMENTOS_INDEX', (data) => {
      this.buscaConta();
      this.buscaMovimentos();
    });
  }
}
</script>
