var b2u = 'https://www.bitcointoyou.com/API/ticker.aspx';
			$.ajax({
			    url: b2u,
			    complete: function(res){
			        var b2uJSON = JSON.parse(res.responseText);
			        var btcvenda = b2uJSON.ticker.sell;
			        var btcvol = b2uJSON.ticker.vol;

			        document.getElementById('btcvenda').innerHTML =btcvenda.substring(0, 7);
			        //document.getElementById('btcvol').innerHTML = btcvol.substring(0, 11);
			        
			    }
			});

/*
			var promaster = 'https://api.promasters.net.br/cotacao/v1/valores?moedas=USD,EUR&alt=json';
			$.ajax({
			    url: promaster,
			    complete: function(res){
			        var promasterJSON = JSON.parse(res.responseText);
			        var usd = promasterJSON.valores.USD.valor;
			        var eur = promasterJSON.valores.EUR.valor;;


					document.getElementById('usd').innerHTML = 'USD: '+usd;
			        document.getElementById('eur').innerHTML = 'EUR: '+eur;


			    }
			});
*/
