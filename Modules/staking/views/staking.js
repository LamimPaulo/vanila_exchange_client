var preco = 0;
  var time = 0;
  var saldo = 0;
  var saldo_stake = 0;
  var regressor = null;
  let balanceVisibility = "";
  const stakingRates = [
    {
      simbolo: "BTC",
      apm: 25,
      bonus: 0,
      penalty: 900,
      min_stake: 0
    },
    {
      simbolo: "CBRL",
      apm: 21,
      bonus: 0,
      penalty: 0,
      min_stake: 0
    },
  ]

  var tokens = {};
  var token = [];
  const handleVisibility = () => {
    const eye = document.getElementById("eye");
    const eye2 = document.getElementById("eye2");
    const eye3 = document.getElementById("eye3");
    if (balanceVisibility == "true") {
      const hidden = document.querySelectorAll(".balance-hidden");
      for (var i = 0; i < hidden.length; i++) {
        hidden[i].style.display = "inline-flex";
      }
      const visible = document.querySelectorAll(".balance-visible");
      for (var i = 0; i < visible.length; i++) {
        visible[i].style.display = "none";
      }
      eye.classList.remove("ion-ios-eye");
      eye.classList.add("ion-ios-eye-off");
      eye2.classList.remove("ion-ios-eye");
      eye2.classList.add("ion-ios-eye-off");
      eye3.classList.remove("ion-ios-eye");
      eye3.classList.add("ion-ios-eye-off");
      balanceVisibility = "false";
      localStorage.setItem("balance_visibility", "false")
    } else {
      const hidden = document.querySelectorAll(".balance-hidden");
      for (var i = 0; i < hidden.length; i++) {
        hidden[i].style.display = "none";
      }
      const visible = document.querySelectorAll(".balance-visible");
      for (var i = 0; i < visible.length; i++) {
        visible[i].style.display = "inline-flex";
      }
      eye.classList.remove("ion-ios-eye-off");
      eye.classList.add("ion-ios-eye");
      eye2.classList.remove("ion-ios-eye-off");
      eye2.classList.add("ion-ios-eye");
      eye3.classList.remove("ion-ios-eye-off");
      eye3.classList.add("ion-ios-eye");
      balanceVisibility = "true";
      localStorage.setItem("balance_visibility", "true")
    }
  }

  if (balanceVisibility == "" && !localStorage.getItem("balance_visibility")) {
    balanceVisibility = "true";
    handleVisibility()
  } else {
    localStorage.getItem("balance_visibility") == "true" ? balanceVisibility = "false" : balanceVisibility = "true";
    handleVisibility()
  }

  document.getElementById("visibility-button").addEventListener("click", handleVisibility);
  document.getElementById("visibility-button2").addEventListener("click", handleVisibility);
  document.getElementById("visibility-button3").addEventListener("click", handleVisibility);

  $(document).ready(function () {
    $("#cryptoFrom").select2();

    $("#cryptoFrom").on('select2:select', function (e) {
      $(".saldo_disponivel").html("");
      saldo = 0;
      var data = $("#cryptoFrom").val();
      const spinner = '<div class="spinner-border spinner-border-sm text-primary" role="status">' +
        '<span class="sr-only">Loading...</span>' +
        '</div>'
      $('.min_stake').html(spinner);
      $('.staked_balance').html(spinner);
      $('.reward_amount').html(spinner);
      $('.accumulated_amount').html(spinner);
      $('#apm_percent').html(spinner)
      carregarSaldo(data);
    });

    var option = '';

    $.ajax({
      url: url_staking_balance,
      method: "POST",
      dataType: 'json',
      success: function (json) {
        if (json.sucesso && json.moedas.length) {
          document.getElementById("staking_spinner_div").style.height = "0vh";
          $("#staking_spinner").hide();
          $("#show_content").show();
          tokens = json.moedas;
          tokens.forEach(element => {
            option += '<option value="' + element.id_moeda + '" title="' + element.imagem + '" data-decimal="' + element.decimal + '">' + element.simbolo + ' - ' + element.nome + '</option>';
          });
          $('#cryptoFrom').html(option);
          const nome = $("#cryptoFrom option:selected").text().replace(" ", "").split("-");
          token = json.moedas.filter(t => t.simbolo === nome[0])
          $('.nome_moeda').html(token[0].nome);
          $('.symbol_moeda').html(token[0].simbolo);
          const tokenReward = token[0].simbolo == "PTQ" ? "PCOIN" : token[0].simbolo;
          $('#reward_period').html(" em <b>" + tokenReward + "</b>");
          saldo = token[0].saldo_disponivel;
          saldo_stake = token[0].staked_balance;
          $(".saldo_disponivel").html(fixDecimals(token[0].saldo_disponivel, token[0].decimal));
          $(".crypto").select2({
            templateResult: function (coin) {
              var $span = $("<span><img style='width: 25px;' src=" + coin.title + " /> " + coin.text + "</span>");
              return $span;
            },
            templateSelection: function (coin) {
              var $span = $("<span><img style='width: 25px;' src=" + coin.title + " /> " + coin.text + "</span>");
              return $span;
            }
          });

          carregarSaldo($("#cryptoFrom").val());
        } else {
          $("#noCoinCard").show();
          showNotyAlert("Sem moedas na carteira, primeiro adquira moedas!", "e");
        }
      }
    });
  });

  async function fetchTxs(page) {
    const update = {
      contract_address: token[0].contract_address,
      user_id: iuserd
    };

    const options = {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(update),
    };

    try {
      const response = await fetch('https://sandbox.coinage.trade/api/priv/staking/transactions?page=' + page, options);
      const data = await response.json();
      const tx = data.data;
      var tBodyHtml = "";
      const tBody_tx = document.getElementById("transactions_table");
      const pagination = document.getElementById("pagination");
      tBody_tx.innerHTML = "";
      pagination.innerHTML = "";
      if (tx.length) {
        for (i = 0; i < tx.length; i++) {
          if (tx[i].staked_coin.simbolo == token[0].simbolo) {
            const rawDate = tx[i].created_at.replace(".000000", "").replace("T", " ").replace("Z", "").split(" ");
            const dateGMT = new Date(rawDate[0] + "T" + rawDate[1] + "Z");
            const date = dateGMT.toLocaleString("pt-BR", { timeZone: "America/Sao_Paulo" }).trim().split(",");
            const amount = parseFloat(tx[i].value).toFixed(token[0].decimal);
            const qde =
              tx[i].type == 1 ? '<span class="text-danger fw-bold">- ' + amount + ' ' + token[0].simbolo + '</span>' :
                tx[i].type == 3 ? '<span class="text-primary">+ ' + amount + ' ' + token[0].simbolo + '</span>' :
                  '<span class="text-success">+ ' + amount + ' ' + token[0].simbolo + '</span>';
            const status = (tx[i].confirmations >= tx[i].confirmations_required ? "<span class='badge badge-success p-2'>Finalizada</span>" : "<span class='badge badge-warning p-2'>Pendente</span>");
            const txid = tx[i].hash;
            const description = tx[i].type == 1 ? "Staking de " + token[0].simbolo : tx[i].type == 2 ? "Recompensa em " + token[0].simbolo : "Resgate de " + token[0].simbolo;

            tBodyHtml += '<tr>' +
              '<td class="align-middle"><img src="' + token[0].imagem + '" height="30" width="30"></td>' +
              '<td class="align-middle"><span>' + description + '</span></td>' +
              '<td class="align-middle"><a href="https://mumbai.polygonscan.com/tx/' + txid + '" target="_blank">Explorer</a></td>' +
              '<th class="fs-12 align-middle p-2"><span>' + date[0] + '</span><span class="d-block">' + date[1] + '</span></th>' +
              '<td class="align-middle">' + qde + '</td>' +
              '<td class="align-middle">' + status + '</td>' +
              '</tr>'

          }
          tBody_tx.innerHTML = tBodyHtml;

          var pages = "";
          for (const item of data.links) {
            const active = item.active == true ? "active" : "";
            const url = item.url == null ? "" : item.label == "&laquo; Anterior" ? data.current_page - 1 : item.label == "Próximo &raquo;" ? data.current_page + 1 : item.label;
            const disabled = item.url == null ? "disabled" : "";
            pages += '<li class="page-item ' + active + ' ' + disabled + '"><a class="page-link" href="javascript:fetchTxs(' + url + ');">' + item.label + '</a></li>'
          }
          const paginationHTML = '<nav class="mx-auto" aria-label="Page navigation example"><ul class="pagination">' + pages + '</ul></nav>';
          pagination.innerHTML = paginationHTML;
        }
      }
    } catch (error) {
      console.error('Data error: ' + error.message);
    }
  }

  function fixDecimals(num, decimal) {
    return (parseFloat(num).toFixed(decimal)).toString().replace('.', ',')
  }

  function setStake() {
    var valor = $('#stake_input').val();
    valor = parseFloat(valor.replace(',', '.'))
    $("#stakingModal").modal("hide");
    $("#waitingTxModal").modal("show");
    $('#stake_input').val("");
    try {
      if (valor <= saldo * 1 && valor > 0) {
        $.ajax(
          {
            url: url_staking_stake,
            method: "POST",
            dataType: 'json',
            data: {
              coin_id: 1, //TODO
              amount: valor,
            },
            success: function (json) {
              if (json.sucesso) {
                showNotyAlert("Transação completa!", "s");
                $("#waitingTxModal").modal("hide");
                $("#completedModal").modal("show");
                setTimeout(() => {
                  location.reload(true);
                }, 3000);
              }
            }
          },
        );
      } else {
        showNotyAlert("Errado!", "e");
      }
    } catch (e) {
      showNotyAlert(e, "e");
    }
  }

  function setResgate() {
    var valor = $('#resgate_input').val();
    valor = parseFloat(valor.replace(',', '.'));
    $("#resgateModal").modal("hide");
    $("#waitingTxModal").modal("show");
    $('#resgate_input').val("");
    try {
      if (valor <= saldo_stake * 1 && valor > 0) {
        // showNotyAlert("Certo!", "s");
        $.ajax(
          {
            url: url_staking_unstake,
            method: "POST",
            dataType: 'json',
            data: {
              coin_id: 1, //TODO
              amount: valor,
            },
            success: function (json) {
              if (json.sucesso) {
                showNotyAlert("Transação completa!", "s");
                $("#waitingTxModal").modal("hide");
                $("#completedModal").modal("show");
                setTimeout(() => {
                  location.reload(true);
                }, 3000);
              }
            }
          },
        );


      } else {
        showNotyAlert("Errado!", "e");
      }
    } catch (e) {
      showNotyAlert(e, "e");
    }
  }

  function inserirSaldo(percent) {
    var saldo_inserido = (saldo * percent / 100);
    $('#stake_input').val(saldo_inserido.toString().replace('.', ','));
    // $('#valor').maskMoney('mask', saldo_inserido);
  };

  function inserirSaldoStake(percent) {
    var saldo_resgate = (saldo_stake * percent / 100);
    $('#resgate_input').val(saldo_resgate.toString().replace(".", ","));
  };


  function getStakedBalance() {
    $.ajax({
      url: url_staking_staked_balance,
      method: "POST",
      dataType: 'json',
      data: {
        contract_address: token[0].contract_address
      },
      success: function (json) {
        saldo_stake = json.valor;
        $('.staked_balance').html(fixDecimals(json.valor, token[0].decimal));
      }
    });
  }

  function getCheckReward() {
    var recompensa = 0;
    $.ajax({
      url: url_staking_check_reward,
      method: "POST",
      dataType: 'json',
      data: {
        contract_address: token[0].contract_address
      },
      success: function (json) {
        recompensa = json.valor;
        $('.reward_amount').html(fixDecimals(json.valor, token[0].decimal));
      }
    });

    $.ajax({
      url: url_staking_check_accumulated_reward,
      method: "POST",
      dataType: 'json',
      data: {
        contract_address: token[0].contract_address
      },
      success: function (json) {
        $('.accumulated_amount').html(fixDecimals(json.valor - recompensa, token[0].decimal));
      }
    });
  }

  function getCurrentApm(stakedToken) {
    var stk = {};
    for (const item of stakingRates) {
      if (stakedToken.simbolo == item.simbolo)
        stk = item;
    }
    const apmFinal = (stk.apm + stk.bonus) / 10 - (stk.apm + stk.bonus) / 10 * (stk.penalty / 1000);
    const min_stake = stk.min_stake;
    $('#apm_percent').html(apmFinal.toString().replace(".", ",") + "%")
    $('.min_stake').html(fixDecimals(min_stake, token[0].decimal));
  }

  function carregarSaldo(moeda) {
    $('#nome_moeda').html("");
    document.getElementById("select_token_spinner").style.display = "none";
    document.getElementById("select_token").style.display = "block";

    $(".crypto").select2({
      templateResult: function (coin) {
        var $span = $("<span><img style='width: 25px;' src=" + coin.title + " /> " + coin.text + "</span>");
        return $span;
      },
      templateSelection: function (coin) {
        var $span = $("<span><img style='width: 25px;' src=" + coin.title + " /> " + coin.text + "</span>");
        return $span;
      }
    });
    const nome = $("#cryptoFrom option:selected").text().replace(" ", "").split("-");
    token = tokens.filter(t => t.simbolo === nome[0])
    $('.nome_moeda').html(nome[1]);
    $('.symbol_moeda').html(nome[0]);
    saldo = token[0].saldo_disponivel;
    $(".saldo_disponivel").html(fixDecimals(token[0].saldo_disponivel, token[0].decimal));
    const tokenReward = token[0].simbolo == "PTQ" ? "PCOIN" : token[0].simbolo;
    $('#reward_period').html(" em <b>" + tokenReward + "</b>");
    getCurrentApm(token[0]);

    $("#stake_input").val("");
    $("#resgate_input").val("");
    fetchTxs()
    getStakedBalance()
    getCheckReward()

     // function getCheckAccumulatedReward() {
  //   $.ajax({
  //     url: "<?php echo URLBASE_CLIENT . \Utils\Rotas::R_STAKING_CHECK_ACCUMULATED_REWARD ?>",
  //     method: "POST",
  //     dataType: 'json',
  //     data: {
  //       contract_address: token[0].contract_address
  //     },
  //     success: function (json) {
  //       $('.accumulated_amount').html(fixDecimals(json.valor, token[0].decimal));
  //     }
  //   });
  // }
  // function getMinStake() {
  //   $.ajax({
  //     url: "<?php echo URLBASE_CLIENT . \Utils\Rotas::R_STAKING_MIN_STAKE ?>",
  //     method: "POST",
  //     dataType: 'json',
  //     data: {
  //       contract_address: token[0].contract_address
  //     },
  //     success: function (json) {
  //       $('.min_stake').html(fixDecimals(json.valor, token[0].decimal));
  //     }
  //   });
  // }

  // var apm = 0;
  // var bonus = 0;
  // var penalty = 0;

  // function getAPM() {
  //   $.ajax({
  //     url: "<?php echo URLBASE_CLIENT . \Utils\Rotas::R_STAKING_GET_APM ?>",
  //     method: "POST",
  //     dataType: 'json',
  //     data: {
  //       contract_address: token[0].contract_address
  //     },
  //     success: function (json) {
  //       apm = json.valor;
  //     }
  //   });
  // }

  // function getBonus() {
  //   $.ajax({
  //     url: "<?php echo URLBASE_CLIENT . \Utils\Rotas::R_STAKING_GET_BONUS ?>",
  //     method: "POST",
  //     dataType: 'json',
  //     data: {
  //       contract_address: token[0].contract_address
  //     },
  //     success: function (json) {
  //       bonus = json.valor;
  //     }
  //   });
  // }

  // function getPenalty() {
  //   $.ajax({
  //     url: "<?php echo URLBASE_CLIENT . \Utils\Rotas::R_STAKING_GET_PENALTY ?>",
  //     method: "POST",
  //     dataType: 'json',
  //     data: {
  //       contract_address: token[0].contract_address
  //     },
  //     success: function (json) {
  //       penalty = json.valor;
  //     }
  //   });
  // }
  }