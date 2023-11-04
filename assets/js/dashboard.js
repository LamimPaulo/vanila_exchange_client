let balanceVisibility = true;

const handleVisibility = () => {
    const eye = document.getElementById("eye");
    if (balanceVisibility == true) {
        const hidden = document.querySelectorAll(".balance-hidden");
        for (var i = 0; i < hidden.length; i++) {
            hidden[i].style.display = "block";
        }
        const visible = document.querySelectorAll(".balance-visible");
        for (var i = 0; i < visible.length; i++) {
            visible[i].style.display = "none";
        }
        eye.classList.remove("ion-ios-eye");
        eye.classList.add("ion-ios-eye-off");
        balanceVisibility = false;
    } else {
        const hidden = document.querySelectorAll(".balance-hidden");
        for (var i = 0; i < hidden.length; i++) {
            hidden[i].style.display = "none";
        }
        const visible = document.querySelectorAll(".balance-visible");
        for (var i = 0; i < visible.length; i++) {
            visible[i].style.display = "block";
        }
        eye.classList.remove("ion-ios-eye-off");
        eye.classList.add("ion-ios-eye");
        balanceVisibility = true;
    }
}

window.addEventListener('load', () => {
    const portfolio = document.querySelector('#portfolio');
    const rentabilidade = document.querySelector('#rentabilidade-table');
    portfolio.classList.toggle('table-responsive', window.matchMedia('(max-width:500px)').matches);
    rentabilidade.classList.toggle('table-responsive', window.matchMedia('(max-width:650px)').matches);
});

window.addEventListener('resize', () => {
    const portfolio = document.querySelector('#portfolio');
    const rentabilidade = document.querySelector('#rentabilidade-table');
    portfolio.classList.toggle('table-responsive', window.matchMedia('(max-width:500px)').matches);
    rentabilidade.classList.toggle('table-responsive', window.matchMedia('(max-width:650px)').matches);
});

document.getElementById("visibility-button").addEventListener("click", handleVisibility);


const proventos_data = [
    {
        "ativo": "BTC",
        "ganho": 0.0009,
        "cbrl_price": 144755.6,
        "data": "19/10/2023"
    },
    {
        "ativo": "BTC",
        "ganho": 0.00012,
        "cbrl_price": 144755.6,
        "data": "19/10/2023"
    },
    {
        "ativo": "FTR",
        "ganho": 5,
        "cbrl_price": 1,
        "data": "19/10/2023"
    },
    {
        "ativo": "BTC",
        "ganho": 0.0007,
        "cbrl_price": 144755.6,
        "data": "18/10/2023"
    },
    {
        "ativo": "BTC",
        "ganho": 0.0009,
        "cbrl_price": 144755.6,
        "data": "18/10/2023"
    },
    {
        "ativo": "FTR",
        "ganho": 15,
        "cbrl_price": 1,
        "data": "18/10/2023"
    },
    {
        "ativo": "BTC",
        "ganho": 0.00025,
        "cbrl_price": 144755.6,
        "data": "15/10/2023"
    },
    {
        "ativo": "ETH",
        "ganho": 0.0021,
        "cbrl_price": 7898.57,
        "data": "15/10/2023"
    },
    {
        "ativo": "ETH",
        "ganho": 0.0012,
        "cbrl_price": 7898.57,
        "data": "12/10/2023"
    },
    {
        "ativo": "BTC",
        "ganho": 0.00053,
        "cbrl_price": 144755.6,
        "data": "11/10/2023"
    },
    {
        "ativo": "FTR",
        "ganho": 24,
        "cbrl_price": 1,
        "data": "10/10/2023"
    },
    {
        "ativo": "FTR",
        "ganho": 9,
        "cbrl_price": 1,
        "data": "10/10/2023"
    },
    {
        "ativo": "BTC",
        "ganho": 0.00048,
        "cbrl_price": 144755.6,
        "data": "05/10/2023"
    },
];

const handleProventos = () => {
    let p = proventos_data;
    var html = "";
    var total = 0;
    for(i=0; i < p.length; i++){
        var price = p[i].ganho * p[i].cbrl_price;
        total = total + price;
        html += `<tr>
                    <td><img class="symbol" src="assets/img/icon/${p[i].ativo}.png" alt="symbol"></td>
                    <td><span class="theme-color">${p[i].ativo}</span></td>
                    <td><span class="balance-hidden">****</span><span
                    class="balance-visible text-success">${p[i].ganho}</span>
                    </td>
                    <td><span class="balance-hidden">****</span><span
                    class="balance-visible text-success">${price.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</span>
                    </td>
                    <td>${p[i].data}</td>
                </tr>`
    }
    document.getElementById("proventos-items").innerHTML = html;
    document.getElementById("proventos-balance").innerHTML = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

const paginate = (current, max) => {
    if (!current || !max) return null

    let prev = current === 1 ? null : current - 1,
        next = current === max ? null : current + 1,
        items = [1]

    if (current === 1 && max === 1) return { current, prev, next, items }
    if (current > 4) items.push('…')

    let r = 2, r1 = current - r, r2 = current + r

    for (let i = r1 > 2 ? r1 : 2; i <= Math.min(max, r2); i++) items.push(i)

    if (r2 + 1 < max) items.push('…')
    if (r2 < max) items.push(max)

    return { current, prev, next, items }
}
var cur = 1;
const handlePagination = (id, data) => {
    let pagination = paginate(cur, Math.ceil(data.length / 2));

    const checkPrev = () => {
        if (pagination.prev == null) {
            return "disabled"
        }
    }

    const checkNext = () => {
        if (pagination.next == null) {
            return "disabled"
        }
    }

    document.getElementById(id).innerHTML = `<li class="page-item ${checkPrev()}" id="pagination_prev">
                                                <a class="page-link" href="#" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                                            <li class="page-item ${checkNext()}" id="pagination_next">
                                                <a class="page-link" href="#" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>`;

    for (i = (cur - 1) * 5; i < 5 * cur; i++) {
        if (data[i] == null) {
        } else {
            console.log(data[i])
        }
    }
}

async function fetchData() {
  try {
    const response = await fetch("https://brasilapi.com.br/api/taxas/v1");
    const data = await response.json();
    return data;
  } catch (error) {
    console.error(`Data error: ${error.message}`);
  }
}

function compareDates() {
    const today = new Date();
    const date1 = new Date(new Date().getFullYear(), 0, 1);

    return (Math.floor((today.getTime() - date1.getTime()) / (1000 * 3600 * 24)));
}

function formatDecimals(num) {
    return num.toFixed(2).replace(".", ",") + "%";
}

const performanceData = async () => {
    const values = await fetchData();
    document.getElementById("cdi-mes").innerHTML = formatDecimals(values[1].valor/12)
    document.getElementById("cdi-sem").innerHTML = formatDecimals(values[1].valor/2)
    document.getElementById("cdi-ano").innerHTML = formatDecimals(values[1].valor * compareDates() / 365)
    document.getElementById("cdi-12m").innerHTML = formatDecimals(values[1].valor)
    document.getElementById("cdi-ini").innerHTML = "0,00%";

    document.getElementById("ipca-mes").innerHTML = formatDecimals(values[2].valor/12)
    document.getElementById("ipca-sem").innerHTML = formatDecimals(values[2].valor/2)
    document.getElementById("ipca-ano").innerHTML = formatDecimals(values[2].valor * compareDates() / 365)
    document.getElementById("ipca-12m").innerHTML = formatDecimals(values[2].valor)
    document.getElementById("ipca-ini").innerHTML = "0,00%";

    document.getElementById("selic-mes").innerHTML = formatDecimals(values[0].valor/12)
    document.getElementById("selic-sem").innerHTML = formatDecimals(values[0].valor/2)
    document.getElementById("selic-ano").innerHTML = formatDecimals(values[0].valor * compareDates() / 365)
    document.getElementById("selic-12m").innerHTML = formatDecimals(values[0].valor)
    document.getElementById("selic-ini").innerHTML = "0,00%";

    document.getElementById("rentabilidade-cdi").innerHTML = formatDecimals(values[1].valor);
}

performanceData();

// handlePagination("proventos_pagination", proventos_data)
handleProventos()


