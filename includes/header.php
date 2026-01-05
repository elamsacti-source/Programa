<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obispado de Huacho - Tesorería</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --color-oro: #C5A059;       
            --color-texto: #1a1a1a;     
            --color-fondo: #F9F7F2;     
            --color-institucional: #2B2B2B; /* Gris muy oscuro, casi negro */
        }

        body { 
            background-color: var(--color-fondo); 
            font-family: 'Lora', serif; 
            color: var(--color-texto);
        }

        /* Barra Superior Específica Huacho */
        .navbar-huacho {
            background-color: var(--color-institucional);
            border-bottom: 4px solid var(--color-oro);
            padding: 1.5rem 0;
        }
        
        .titulo-diocesis {
            font-family: 'Cinzel', serif;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 1.5rem;
            text-align: center;
        }
        
        .subtitulo-diocesis {
            color: var(--color-oro);
            font-size: 0.9rem;
            letter-spacing: 1px;
            display: block;
            margin-top: 5px;
            text-align: center;
        }

        /* Estilos generales */
        .card { border-radius: 0; border: 1px solid #ddd; }
        .btn { border-radius: 2px; font-family: 'Cinzel', serif; }
    </style>
</head>
<body>

<nav class="navbar navbar-huacho mb-5">
  <div class="container justify-content-center">
    <div>
        <div class="titulo-diocesis">✝ DIÓCESIS DE HUACHO</div>
        <span class="subtitulo-diocesis">Sistema de Gestión Curial</span>
    </div>
  </div>
</nav>

<div class="container pb-5">