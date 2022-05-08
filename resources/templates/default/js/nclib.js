

(function(w) {
  'use strict';

  const BITFINEX = "wss://api.bitfinex.com/ws/2";
  const POLONIEX = "wss://api.poloniex.com";
  const BITCOIN2U = "https://www.bitcointoyou.com/API/ticker.aspx";
  const MERCADOBTC = "https://www.mercadobitcoin.net/api/ticker/";
  const BITSTAMP = "https://www.bitstamp.net/api/v2/ticker/btcusd/";
  const FOXBIT= "https://api.blinktrade.com/api/v1/BRL/ticker?crypto_currency=BTC";
  // const BLOCKCHAIN = "https://blockchain.info/pt/ticker";

  function xhrGet(url, callbackProgress) {
    return new Promise((resolve, reject) => {
      let xhr = new XMLHttpRequest();
      xhr.open('GET', url, true);
      xhr.addEventListener("readystatechange", (e) => {
        let req = e.target;
        if (req.readyState == 4) {
          if (req.status == 200) {
            resolve(JSON.parse(req.responseText));
          } else {
            reject('Erro: status recebido: ' + req.status);
          }
        }
      });
      xhr.addEventListener("error", (e) => {
        reject('Erro: request inválido.');
      });
      xhr.addEventListener("timeout", (e) => {
        reject('Erro: timeout na requisição xhr.');
      });
      xhr.addEventListener("progress", (e) => {
        if (typeof callbackProgress !== "function") {
          return;
        }
        if (e.lengthComputable) {
          let loaded = e.loaded;
          let total = e.total;
          let p = loaded / total;
        } else {
        }
      });
      xhr.send(null);
    });
  }

  class CotacaoB2U {

    constructor() {

    }

    abrirConexao(cb) {
      let self = this;
      let callback = cb;

      function atualiza() {
        xhrGet(BITCOIN2U)
          .then((data) => {
            if (data.hasOwnProperty('ticker')) {
              let tic = data.ticker;
              callback(parseFloat(tic.vol), parseFloat(tic.buy), parseFloat(tic.sell));
            }
          })
          .catch((err) => {
          });
        setTimeout(() => atualiza(), 1000);
      }

      atualiza();
    }
  }
 
  w.CotacaoB2U = CotacaoB2U;


  class CotacaoNC {

    constructor() {

    }

    abrirConexao(cb) {
      let self = this;
      let callback = cb;

      function atualiza() {
        xhrGet(BITCOIN2U)
          .then((data) => {
            if (data.hasOwnProperty('ticker')) {
              let tic = data.ticker;

              const buy1 = parseFloat(tic.buy) - parseFloat(tic.buy * 0.03 );
              const sell1 = parseFloat(tic.sell * 0.03 ) + parseFloat(tic.sell);
              callback(parseFloat(tic.vol), parseFloat(buy1), parseFloat(sell1));
            }
          })
          .catch((err) => {
          });
        setTimeout(() => atualiza(), 1000);
      }

      atualiza();
    }
  }
  w.CotacaoNC = CotacaoNC;



  /*
  class CotacaoBlockchain {

    constructor() {

    }

    abrirConexao(cb) {
      let self = this;
      let callback = cb;

      function atualiza() {
        xhrGet(BLOCKCHAIN)
          .then((data) => {
            if (data.hasOwnProperty('USD')) {
              let tic = data.USD;
              callback(0, tic.buy, tic.sell);
            }
          })
          .catch((err) => {
          });
        setTimeout(() => atualiza(), 1000);
      }

      atualiza();
    }
  }
  w.CotacaoBlockchain = CotacaoBlockchain;
  */

  class CotacaoFoxBit {
    constructor() {

    }
    abrirConexao(cb) {
      let self = this;
      let callback = cb;

      function atualiza() {
        xhrGet(FOXBIT)
          .then((data) => {
              callback(parseFloat(data.vol), parseFloat(data.buy), parseFloat(data.sell));
          })
          .catch((err) => {
          });
        setTimeout(() => atualiza(), 1000);
      }

      atualiza();
    }
  }
  w.CotacaoFoxBit = CotacaoFoxBit;

  class CotacaoMercado {
    constructor(cb) {

    }
    abrirConexao(cb) {
      let self = this;
      let callback = cb;

      function atualiza() {
        xhrGet(MERCADOBTC)
          .then((data) => {
            if (data.hasOwnProperty('ticker')) {
              let tic = data.ticker;

              callback(tic.vol, tic.buy, tic.sell);
            }
          })
          .catch((err) => {
          });
        setTimeout(() => atualiza(), 1000);
      }

      atualiza();
    }
  }
  w.CotacaoMercado = CotacaoMercado;

/*
  class CotacaoBitStamp {
    constructor() {

    }
    abrirConexao(cb) {
      let self = this;
      let callback = cb;

      function atualiza() {

        xhrGet(BITSTAMP)
          .then((data) => {
            callback(data.volume, data.bid, data.ask);
          })
          .catch((err) => {
          });
        setTimeout(() => atualiza(), 1000);
      }

      atualiza();
    }
  }
  w.CotacaoBitStamp = CotacaoBitStamp;
*/
  class CotacaoPolo {

    constructor() {
      this.conexao = null;
    }

    abrirConexao(cb) {
      this.conn = new autobahn.Connection({
          url: POLONIEX,
          realm: "realm1",
          max_retries: -1,
          use_es6_promises: true
      });
      this.callback = cb;
      this.conn.onopen = (session) => this.onOpen(session);
      this.conn.onclose = (reason, details) => this.onClose(reason, details);
      this.conn.open();
    }

    evtMercado(args, kwargs) {
      if (args.length > 0 && args[0] == "USDT_BTC") {
        this.callback(parseFloat(args[6]), parseFloat(args[3]), parseFloat(args[2]));
      }
    }

    onOpen(session) {
      this.conn.session.subscribe("ticker", (args, kwargs) => this.evtMercado(args, kwargs));
    }

    onClose(reason, details) {
      console.log('Conexao fechada: razão: ' + reason + ', detalhes: ' + details);
    }
  }
  w.CotacaoPolo = CotacaoPolo;

  class CotacaoBF {

    constructor() {
      this.wss = null;
    }

    abrirConexao(cb) {
      let callback = cb;

      this.wss = new WebSocket(BITFINEX);

      this.wss.onopen = (e) => {
        let ws = e.target;

        let msg = JSON.stringify({
          "event": "subscribe",
          "channel": "ticker",
          "symbol": "tBTCUSD"
        });
        ws.send(msg);
      };
      this.wss.onclose = (e) => {

      };
      this.wss.onmessage = (msg) => {
        let data = JSON.parse(msg.data);
        if (Array.isArray(data) && Array.isArray(data[1])) {
          callback(data[1][7], data[1][0], data[1][2]);
        }
      };
    }

  }
  w.CotacaoBF = CotacaoBF;

})(window || {});
