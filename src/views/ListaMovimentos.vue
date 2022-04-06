<style>

</style>

<template>
  <div>
    <div class="page">

      <div class="box">
        <div class="pageTitle">Movimentos</div>
      </div>

      <div class="whitebox mt10">
        <div class="pageSubtitle">Criar Movimento</div>
        <div class="flex-column">
          <input name="descricao" class="form-input" type="text" placeholder="descricao" v-model="novoMovimentoDescricao">
          <input name="nomeLoja" class="form-input" type="text" placeholder="nomeLoja" v-model="novoMovimentoNomeLoja">
          <input name="valor" class="form-input" type="money" placeholder="valor" v-model="novoMovimentoValor">
          <input name="data" class="form-input" type="date" placeholder="data" v-model="novoMovimentoData">

          <span class="mt10">Lista de Itens do Movimento</span>
          
          <div class="whitebox mt10 flex justify-spacebetween alignitens-start" v-for="item in listaNovosItensMovimentos" :key="item.id">
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

          <span class="mt10">
            <button class="form-input btn" @click="criarMovimento()">Criar movimento</button>
          </span>
        </div>
      </div>

      <div class="whitebox mt10 flex-column">
        <div class="pageSubtitle">Dados da conta</div>
        <span class="mt10">{{conta.nome}} :: Total R$ {{conta.saldo}}</span>
        <span class="mt10">Total Entradas: {{totais.totalEntradas}}</span>
        <span class="mt10">Total Saídas: {{totais.totalSaidas}}</span>
        <span class="mt10">Saldo Inicial: {{totais.saldoInicial}}</span>
        <span class="mt10">Saldo Final do Período: {{totais.saldoFinalPeriodo}}</span>
      </div>

      <div class="whitebox mt10">
        <div class="pageSubtitle">Movimentos</div>
        <div class="">
          <input name="filtroPorMes" class="form-input-inline form-input-sm" type="text" placeholder="filtroPorMes" v-model="filtroPorMes">
          <button class="btn btn-sm" @click="buscaMovimentos()"><i class="fas fa-edit"></i> Buscar</button>
        </div>

        <div class="whitebox max-width-800 mt10 flex-column font-size-small" v-for="movimento in movimentos" :key="movimento.id">
          <span class="mv5">
            {{ movimento.nomeLoja }} *** {{ movimento.descricao }}
          </span>
          <span class="mv5 align-right">
            Total: R$ {{ movimento.valor }}
          </span>
          <span class="mv5 align-right" v-for="item in movimento.itensMovimentos" :key="item.id">
            {{item.nome}} R$ {{item.valor}}
          </span>
          <span class="mv5">
            {{ movimento.dataExibicao }}
          </span>
          <span class="mv5">
            <button class="btn btn-sm" @click="ativarModalEdicao(movimento.id)"><i class="fas fa-edit"></i> Editar</button>
            <button class="btn btn-sm ml5" @click="excluirMovimento(movimento.id)"><i class="fas fa-trash-alt"></i> Excluir</button>
          </span>
        </div>
      </div>

      <!-- <div class="whitebox">
        <div class="pageSubtitle">Movimentos</div>
        <table class="listaMovimentos">
          <tr>
            <th>descricao</th>
            <th class="align-right">valor</th>
            <th class="align-right">data</th>
            <th class="align-right">acoes</th>
          </tr>
          <tr v-for="movimento in movimentos" :key="movimento.id">
            <td>{{ movimento.nomeLoja }} *** {{ movimento.descricao }}</td>
            <td class="align-right">{{ movimento.valor }}</td>
            <td class="align-right">{{ movimento.dataExibicao }}</td>
            <td class="align-right">
              <button class="btn" @click="ativarModalEdicao(movimento.id)"><i class="fas fa-edit"></i></button>
              <button class="btn" @click="excluirMovimento(movimento.id)"><i class="fas fa-trash-alt"></i></button>
            </td>
          </tr>
        </table>
      </div> -->
      
      <Loader :busy="busy"></Loader>

      <ModalEditarMovimento :exibirModalEdicao.sync="exibirModalEdicao" :movimento="movimentoEditar"></ModalEditarMovimento>
    </div>
  </div>
</template>

<script>
import EventBus from '@/core/EventBus.js';
import notify from '@/core/notify.js';
import Loader from '@/components/Loader.vue';
import ModalEditarMovimento from '@/views/ModalEditarMovimento.vue';

export default {
  name: 'ListaContas',
  components: {
    Loader,
    ModalEditarMovimento,
  },
  props: {
    idConta: String
  },
  data: () => {
    return {
      busy: false,
      conta: {},
      novoMovimentoDescricao: '',
      novoMovimentoNomeLoja: '',
      novoMovimentoValor: '',
      novoMovimentoData: '',
      novoItemMovimentoNome: '',
      novoItemMovimentoValor: '',
      listaNovosItensMovimentos: [],

      buscarMovimentoOrdemDecrescente: true,
      movimentos: [],
      exibirModalEdicao: false,
      movimentoEditar: {},

      filtroPorMes: '',
      totais: {
        totalEntradas: 0,
        totalSaidas: 0,
        saldoInicial: 0,
        saldoFinalPeriodo: 0
      }
    }
  },
  methods: {
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
        url += `&order-data=desc`
      }
      if(this.filtroPorMes !== '') {
        url += `&mes=${this.filtroPorMes}`
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
        this.totais = this.processaTotais(data.totais);
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

        for (let j = 0; j < movimento.itensMovimentos.length; j++) {
          movimento.itensMovimentos[j].valor = movimento.itensMovimentos[j].valor.replace('.',',');
        }

        movimento.data = movimento.data.date.substr(0, 10);
      });
      return movimentos;
    },
    processaTotais(totais){
      totais.totalEntradas = totais.totalEntradas.toString().replace('.',',');
      totais.totalSaidas = totais.totalSaidas.toString().replace('.',',');
      totais.saldoInicial = totais.saldoInicial.toString().replace('.',',');
      totais.saldoFinalPeriodo = totais.saldoFinalPeriodo.toString().replace('.',',');
      return totais;
    },
    adicionarItemMovimento(){
      //defino o novo id
      let id = 1;
      for (let i = 0; i < this.listaNovosItensMovimentos.length; i++) {
        const element = this.listaNovosItensMovimentos[i];
        id = element.id > id ? element.id : id;
      }
      id++;
      //crio o item vazio e coloco no array
      let novoItem = {id: id, nome: '',valor: ''};
      this.listaNovosItensMovimentos.push(novoItem);
    },
    retirarItemMovimento(id){
      this.listaNovosItensMovimentos.splice(this.listaNovosItensMovimentos.findIndex(function(item){
        return item.id === id;
      }), 1);
    },
    criarMovimento() {
      //VALIDAR O listaNovosItensMovimentos
      if(this.listaNovosItensMovimentos.length < 1) {
        alert('preenche item');
        return;
      } else {
        for (let i = 0; i < this.listaNovosItensMovimentos.length; i++) {
          const item = this.listaNovosItensMovimentos[i];
          if(item.nome == '') {
            alert('preenche o nome do item');
            return;
          } else if (item.valor == '') {
            alert('preenche o valor do item');
            return;
          }
        }
      }
      this.busy = true;
      let url = 'http://localhost:8000/movimentos';
      let body = {
        'descricao': this.novoMovimentoDescricao,
        'nomeLoja': this.novoMovimentoNomeLoja,
        'valor': this.novoMovimentoValor,
        'data': this.novoMovimentoData,
        'idConta' : this.idConta,
        'itensMovimentos': this.listaNovosItensMovimentos
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
      this.novoMovimentoNomeLoja = null;
      this.novoMovimentoValor = null;
      this.listaNovosItensMovimentos = [];
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
