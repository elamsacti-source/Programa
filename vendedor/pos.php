<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>POS</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        /* Ajustes finales para que el modal se vea centrado y limpio */
        .modal-box { 
            min-height: 200px; 
            display: flex; 
            flex-direction: column; 
            justify-content: center;
        }
        /* El nombre del producto vacío no ocupará espacio hasta que se llene */
        #m-name { min-height: 1.2em; }
    </style>
</head>
<body>

    <div class="navbar">
        <span class="brand">VENTAS</span>
        <a href="../index.php?logout=1" style="color: #666; text-decoration: none; font-size: 0.7rem; border: 1px solid #333; padding: 5px 12px; border-radius: 20px;">SALIR</a>
    </div>

    <div class="pos-layout">
        <div class="catalog-area">
            <input type="text" id="search" placeholder="BUSCAR..." style="border-radius: 50px; background: #000; text-transform: uppercase;">
            
            <div id="grid" class="prod-grid"></div>
        </div>
    </div>

    <div class="fab" onclick="toggleCart()">
        <span>TICKET</span>
        <span>S/ <span id="fab-total">0.00</span></span>
    </div>

    <div id="cart-screen" class="overlay">
        <div class="navbar" style="background: #000;">
            <span class="brand" style="color:#fff;">DETALLE</span>
            <button onclick="toggleCart()" style="background:none; border:none; color:#fff; font-size:1.5rem; padding:10px;">✕</button>
        </div>
        
        <div id="cart-list" style="flex:1; overflow-y:auto; padding:15px;"></div>

        <div style="padding:20px; background: #0a0a0a; border-top: 1px solid var(--gold);">
            <div style="display:flex; justify-content:space-between; margin-bottom: 10px; align-items: flex-end;">
                <span style="color: #666; font-size: 0.8rem;">TOTAL</span>
                <span style="color: var(--gold); font-size: 2rem; font-weight: 800; line-height: 1;">S/ <span id="total-txt">0.00</span></span>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="color:#666; font-size:0.7rem;">MÉTODO DE PAGO</label>
                <select id="sel-pago" style="margin-top:5px; border:1px solid var(--gold); background:#111; color:#fff;">
                    <option value="EFECTIVO">💵 EFECTIVO</option>
                    <option value="YAPE">🟣 YAPE / PLIN</option>
                    <option value="TARJETA">💳 TARJETA</option>
                </select>
            </div>
            <button onclick="pay()" id="btn-pay" class="btn btn-primary" disabled>CONFIRMAR VENTA</button>
        </div>
    </div>

    <div id="modal" class="modal-bg">
        <div class="modal-box">
            
            <h3 id="m-name" style="text-align:center; margin-bottom:5px; color:#fff; font-size:1.1rem;"></h3>
            <p id="m-stock" style="text-align:center; color:#555; font-size:0.7rem; margin-bottom:20px; text-transform:uppercase;"></p>

            <div id="tabs" class="tab-row" style="display:none;">
                <div class="tab active" id="t-money" onclick="setMode('money')">DINERO (S/)</div>
                <div class="tab" id="t-weight" onclick="setMode('weight')">PESO (Kg)</div>
            </div>

            <input type="number" id="m-val" class="big-input" placeholder="0">
            
            <div id="m-res" style="text-align:center; margin-bottom: 20px; color: var(--gold); font-weight: 800; font-size: 1.1rem; min-height: 1.5rem;"></div>

            <div style="display:flex; gap: 10px;">
                <button onclick="closeModal()" class="btn btn-dark">CANCELAR</button>
                <button onclick="addCart()" class="btn btn-primary">CONFIRMAR</button>
            </div>
        </div>
    </div>

    <script>
        let cart=[], pSel=null, mode='unit'; const uId=<?= $_SESSION['user_id'] ?>;
        
        document.addEventListener('DOMContentLoaded', ()=>load(''));
        document.getElementById('search').addEventListener('keyup', (e)=>load(e.target.value));

        async function load(q) {
            try {
                const r = await fetch(`../api/buscar_producto.php?q=${q}`);
                const d = await r.json();
                const g = document.getElementById('grid'); g.innerHTML='';
                
                if(d.length === 0) {
                    g.innerHTML = '<div style="grid-column: span 2; text-align:center; color:#444; margin-top:30px; font-size:0.8rem;">SIN RESULTADOS</div>';
                    return;
                }

                d.forEach(p=>{
                    const gr = p.es_granel==1;
                    
                    // RENDERIZADO COMPACTO (Sin badges, solo info crucial)
                    g.innerHTML += `
                    <div class="prod-card" onclick='openM(${JSON.stringify(p)})'>
                        <div class="prod-name">${p.nombre}</div>
                        
                        <div class="info-row">
                            <div class="stock-display">STOCK: ${parseFloat(p.stock).toFixed(gr?3:0)}</div>
                            <div class="price-display">S/ ${parseFloat(p.precio).toFixed(2)}</div>
                        </div>
                    </div>`;
                });
            } catch(e){}
        }

        function openM(p){
            pSel=p; 
            document.getElementById('m-name').innerText = p.nombre;
            document.getElementById('m-stock').innerText = `DISPONIBLE: ${parseFloat(p.stock).toFixed(3)}`;
            
            document.getElementById('modal').style.display='flex';
            
            const t=document.getElementById('tabs');
            
            // Lógica inteligente de tabs
            if(p.es_granel==1){ t.style.display='flex'; setMode('money'); }
            else{ t.style.display='none'; setMode('unit'); }
            
            document.getElementById('m-val').value=''; 
            document.getElementById('m-res').innerText = '...';
            setTimeout(()=>document.getElementById('m-val').focus(),100);
        }

        function setMode(m){
            mode=m; document.querySelectorAll('.tab').forEach(x=>x.classList.remove('active'));
            if(m=='money'){ document.getElementById('t-money').classList.add('active'); document.getElementById('m-val').placeholder='S/ 0.00'; }
            else if(m=='weight'){ document.getElementById('t-weight').classList.add('active'); document.getElementById('m-val').placeholder='0.000 Kg'; }
            else document.getElementById('m-val').placeholder='CANTIDAD';
            calc();
        }

        document.getElementById('m-val').addEventListener('input', calc);
        
        function calc(){
            const v=parseFloat(document.getElementById('m-val').value)||0; const d=document.getElementById('m-res');
            
            if(v<=0){ d.innerText='...'; return; }
            
            if(mode=='unit') d.innerText=`TOTAL: S/ ${(v*pSel.precio).toFixed(2)}`;
            else if(mode=='money') d.innerText=`PESO: ${(v/pSel.precio).toFixed(3)} Kg`;
            else d.innerText=`COBRAR: S/ ${(v*pSel.precio).toFixed(2)}`;
        }

        function addCart(){
            const v=parseFloat(document.getElementById('m-val').value); if(!v||v<=0)return;
            let q=v, s=v*pSel.precio;
            if(mode=='money'){ s=v; q=s/pSel.precio; }
            cart.push({...pSel, qty:q, sub:s}); closeModal(); render();
        }
        function closeModal(){ document.getElementById('modal').style.display='none'; }
        
        function render(){
            const l=document.getElementById('cart-list'); l.innerHTML=''; let t=0;
            if(cart.length==0) l.innerHTML='<div style="text-align:center; color:#444; margin-top:50px; font-size:0.9rem;">CARRITO VACÍO</div>';
            cart.forEach((i,x)=>{
                t+=i.sub;
                l.innerHTML+=`
                <div style="border-bottom:1px solid #222; padding-bottom:15px; margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="font-weight:700; color:#fff; font-size:0.9rem;">${i.nombre}</div>
                        <div style="font-size:0.75rem; color:#666;">${i.es_granel==1?i.qty.toFixed(3)+' Kg':i.qty+' Und'}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="color:var(--gold); font-weight:700; font-size:1rem;">S/ ${i.sub.toFixed(2)}</div>
                        <button onclick="del(${x})" style="color:#b91c1c; background:none; border:none; font-size:0.7rem; padding:5px 0;">ELIMINAR</button>
                    </div>
                </div>`;
            });
            const ft=t.toFixed(2);
            document.getElementById('total-txt').innerText=ft;
            document.getElementById('fab-total').innerText=ft;
            document.getElementById('btn-pay').disabled=cart.length==0;
        }
        function del(i){ cart.splice(i,1); render(); }
        
        function toggleCart(){ 
            const c = document.getElementById('cart-screen');
            if(c.classList.contains('open')) c.classList.remove('open');
            else c.classList.add('open');
        }

        async function pay(){
            const b=document.getElementById('btn-pay'); 
            const metodo = document.getElementById('sel-pago').value; // CAMBIO: Obtener método

            b.innerText='PROCESANDO...';
            try{
                // CAMBIO: Enviar metodo_pago en el cuerpo
                const r=await fetch('../api/guardar_venta.php',{
                    method:'POST',
                    body:JSON.stringify({
                        usuario_id:uId, 
                        metodo_pago: metodo,
                        items:cart.map(i=>({...i, cantidad:i.qty, subtotal:i.sub}))
                    })
                });
                const d=await r.json();
                if(d.success){ alert('✅ VENTA EXITOSA'); cart=[]; render(); toggleCart(); load(''); }
                else alert(d.message);
            }catch(e){alert('ERROR DE CONEXIÓN');}
            b.innerText='CONFIRMAR VENTA';
        }
    </script>
</body>
</html>