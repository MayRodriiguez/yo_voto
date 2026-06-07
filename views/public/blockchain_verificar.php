<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Blockchain - Yo Voto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #003399, #1a5bc4, #f5c518); font-family: 'Segoe UI', sans-serif; }
        .navbar { background: rgba(0,51,153,0.95); padding: 15px 40px; }
        .logo { font-size: 28px; font-weight: bold; color: #f5c518; }
        .logo span { color: white; }
        .container-custom { max-width: 1200px; margin: 40px auto; padding: 20px; }
        .card-blockchain { background: white; border-radius: 20px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .card-header-custom { background: linear-gradient(135deg, #003399, #1a5bc4); color: white; padding: 15px 20px; border-radius: 15px 15px 0 0; margin: -30px -30px 20px -30px; }
        .stat-number { font-size: 36px; font-weight: bold; color: #003399; }
        .hash-text { font-family: monospace; font-size: 12px; background: #f0f0f0; padding: 5px; border-radius: 5px; word-break: break-all; }
        .block-card { background: #f8f5ff; border-radius: 15px; padding: 15px; margin-bottom: 15px; border-left: 5px solid #f5c518; }
        .badge-valid { background: #4caf50; color: white; padding: 5px 15px; border-radius: 20px; }
        .badge-invalid { background: #dc2626; color: white; padding: 5px 15px; border-radius: 20px; }
        .search-box { background: white; border-radius: 20px; padding: 20px; margin-bottom: 30px; }
        footer { text-align: center; padding: 20px; color: white; margin-top: 40px; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo"> Yo <span>Voto</span></div>
        <div class="nav-links">
            <a href="/yo_voto/" style="color: white; text-decoration: none; margin-left: 20px;">Inicio</a>
            <a href="/yo_voto/resultados" style="color: white; text-decoration: none; margin-left: 20px;">Resultados</a>
            <a href="/yo_voto/blockchain-verificar" style="color: #f5c518; text-decoration: none; margin-left: 20px;">🔗 Blockchain</a>
        </div>
    </nav>

    <div class="container-custom">
        <div class="card-blockchain">
            <div class="card-header-custom">
                <h2><i class="fas fa-link"></i> Verificador de Blockchain</h2>
                <p>Todos los votos están registrados en una cadena de bloques inmutable</p>
            </div>
            
            <!-- Estadísticas -->
            <div class="row" id="estadisticas">
                <div class="col-md-3 text-center">
                    <div class="stat-number" id="total-bloques">-</div>
                    <small>Total Bloques</small>
                </div>
                <div class="col-md-3 text-center">
                    <div class="stat-number" id="total-votos">-</div>
                    <small>Votos Registrados</small>
                </div>
                <div class="col-md-3 text-center">
                    <div class="stat-number" id="dificultad">-</div>
                    <small>Dificultad de Minado</small>
                </div>
                <div class="col-md-3 text-center">
                    <div id="estado-cadena"></div>
                    <small>Estado Blockchain</small>
                </div>
            </div>
        </div>

        <!-- Buscar bloque por hash -->
        <div class="search-box">
            <h4><i class="fas fa-search"></i> Verificar un bloque específico</h4>
            <div class="input-group mt-3">
                <input type="text" id="hash-buscar" class="form-control" placeholder="Ingrese el hash del bloque...">
                <button class="btn btn-primary" onclick="buscarBloque()">Buscar</button>
            </div>
            <div id="resultado-busqueda" class="mt-3" style="display: none;"></div>
        </div>

        <!-- Cadena de bloques -->
        <div class="card-blockchain">
            <h4><i class="fas fa-cubes"></i> Últimos Bloques de la Cadena</h4>
            <div id="cadena-bloques" class="mt-3">
                <div class="text-center">Cargando blockchain...</div>
            </div>
        </div>
    </div>

    <footer>
        <p>Yo Voto - Sistema Electoral Bolivia 2026 | Votos verificables con Blockchain</p>
    </footer>

    <script>
        async function cargarEstadisticas() {
            try {
                const response = await fetch('/yo_voto/api/blockchain_api.php?action=estadisticas');
                const data = await response.json();
                
                document.getElementById('total-bloques').innerHTML = data.total_bloques || 0;
                document.getElementById('total-votos').innerHTML = data.total_votos || 0;
                document.getElementById('dificultad').innerHTML = data.dificultad || 2;
                
                const estadoDiv = document.getElementById('estado-cadena');
                if (data.cadena_valida) {
                    estadoDiv.innerHTML = '<span class="badge-valid"><i class="fas fa-check-circle"></i> Válida</span>';
                } else {
                    estadoDiv.innerHTML = '<span class="badge-invalid"><i class="fas fa-exclamation-triangle"></i> Corrupta</span>';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function cargarCadena() {
            try {
                const response = await fetch('/yo_voto/api/blockchain_api.php?action=cadena&limit=20');
                const bloques = await response.json();
                
                if (!bloques.length) {
                    document.getElementById('cadena-bloques').innerHTML = '<div class="text-center">No hay bloques registrados</div>';
                    return;
                }
                
                let html = '';
                for (let i = bloques.length - 1; i >= 0; i--) {
                    const b = bloques[i];
                    const datos = typeof b.datos_voto === 'string' ? JSON.parse(b.datos_voto) : b.datos_voto;
                    const esGenesis = b.indice === 0;
                    
                    html += `
                        <div class="block-card">
                            <div class="row">
                                <div class="col-md-2">
                                    <strong>Bloque #${b.indice}</strong>
                                    <br>
                                    <small>${new Date(b.timestamp * 1000).toLocaleString()}</small>
                                </div>
                                <div class="col-md-5">
                                    <small><strong>Hash:</strong></small>
                                    <div class="hash-text">${b.hash_bloque.substring(0, 40)}...</div>
                                    <small><strong>Hash Anterior:</strong></small>
                                    <div class="hash-text">${b.hash_anterior === '0' ? 'Genesis' : b.hash_anterior.substring(0, 30) + '...'}</div>
                                </div>
                                <div class="col-md-5">
                                    ${!esGenesis ? `
                                        <small><strong>Voto:</strong></small>
                                        <div>Candidato ID: ${datos.id_candidato || 'N/A'}</div>
                                        <div>Usuario Hash: ${(datos.id_usuario || '').substring(0, 20)}...</div>
                                        <div>Timestamp: ${new Date(datos.timestamp_voto * 1000).toLocaleString()}</div>
                                    ` : `
                                        <small><strong>Bloque Génesis</strong></small>
                                        <div>${datos.mensaje || 'Inicio de la blockchain'}</div>
                                    `}
                                </div>
                            </div>
                        </div>
                    `;
                }
                document.getElementById('cadena-bloques').innerHTML = html;
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('cadena-bloques').innerHTML = '<div class="text-center text-danger">Error al cargar la blockchain</div>';
            }
        }

        async function buscarBloque() {
            const hash = document.getElementById('hash-buscar').value.trim();
            if (!hash) {
                alert('Ingrese un hash válido');
                return;
            }
            
            try {
                const response = await fetch(`/yo_voto/api/blockchain_api.php?action=buscar&hash=${encodeURIComponent(hash)}`);
                const bloque = await response.json();
                const resultadoDiv = document.getElementById('resultado-busqueda');
                
                if (bloque.error) {
                    resultadoDiv.innerHTML = `<div class="alert alert-danger">${bloque.error}</div>`;
                } else {
                    const datos = typeof bloque.datos_voto === 'string' ? JSON.parse(bloque.datos_voto) : bloque.datos_voto;
                    resultadoDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong> Bloque encontrado!</strong><br>
                            <strong>Índice:</strong> ${bloque.indice}<br>
                            <strong>Hash:</strong> <code>${bloque.hash_bloque}</code><br>
                            <strong>Hash Anterior:</strong> <code>${bloque.hash_anterior}</code><br>
                            <strong>Timestamp:</strong> ${new Date(bloque.timestamp * 1000).toLocaleString()}<br>
                            <strong>Datos:</strong> <pre class="mt-2">${JSON.stringify(datos, null, 2)}</pre>
                        </div>
                    `;
                }
                resultadoDiv.style.display = 'block';
            } catch (error) {
                document.getElementById('resultado-busqueda').innerHTML = `<div class="alert alert-danger">Error al buscar</div>`;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarEstadisticas();
            cargarCadena();
            // Actualizar cada 30 segundos
            setInterval(() => {
                cargarEstadisticas();
                cargarCadena();
            }, 30000);
        });
    </script>
</body>
</html>