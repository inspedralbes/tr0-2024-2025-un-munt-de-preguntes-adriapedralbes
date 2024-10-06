"use strict";

let iniciarButton = document.getElementById("iniciar-joc");
let preguntesTotals = document.getElementById("preguntes-totals"); 
const veureResultatsButton = document.getElementById("veure-resultats");
let intervalId;
let segundos = 0;
let minutos = 0;

let preguntesQuiz = [];
let indexPreguntaActual = 0;

iniciarButton.addEventListener("click", iniciarJoc);

function guardarNomUsuari(nomUsuari) {
    localStorage.setItem("nomUsuari", nomUsuari);
}

function obtenirPreguntes(numPreguntes) {
    fetch(`http://localhost/decero/back/getPreguntes.php?num_preguntes=${numPreguntes}`)
      .then(resposta => resposta.json())
      .then(dades => {
        preguntesQuiz = dades;
        indexPreguntaActual = 0;
        mostrarPregunta();
        actualitzarNavegacio();
      })
      .catch(error => {
        console.error('Error:', error);
      });
}

function mostrarPregunta() {
    if (indexPreguntaActual >= preguntesQuiz.length) {
        console.log("No hi ha més preguntes");
        return;
    }

    const pregunta = preguntesQuiz[indexPreguntaActual];
    const divPregunta = document.getElementById("pregunta");
    const divRespostes = document.getElementById("respostes");

    divPregunta.innerHTML = `<h3>${pregunta.pregunta}</h3>`;
    divRespostes.innerHTML = "";

    pregunta.respostes.forEach((resposta, index) => {
        const buttonResposta = document.createElement("button");
        buttonResposta.innerHTML = resposta.resposta;
        buttonResposta.onclick = () => seleccionarResposta(index);
        if (resposta.imatge) {
            const img = document.createElement("img");
            img.src = resposta.imatge;
            img.alt = "Imatge de la resposta";
            img.style.maxWidth = "100px";
            buttonResposta.prepend(img);
        }
        divRespostes.appendChild(buttonResposta);
    });

    document.getElementById("pregunta-actual").textContent = indexPreguntaActual + 1;
    document.getElementById("preguntes-totals").textContent = preguntesQuiz.length;
}

function seleccionarResposta(indexResposta) {
    const pregunta = preguntesQuiz[indexPreguntaActual];

    // Si la pregunta ya ha sido respondida, no hacemos nada
    if (pregunta.hasOwnProperty('respostaSeleccionada')) {
        console.log('Aquesta pregunta ja ha estat contestada');
        return;
    }

    pregunta.respostaSeleccionada = indexResposta;
    
    // Enviar la respuesta al servidor
    fetch('http://localhost/decero/back/guardarResposta.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `pregunta_id=${pregunta.id}&resposta_index=${indexResposta}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Resposta guardada correctament');
        } else {
            console.error('Error al guardar la resposta:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));

    console.log(`Resposta seleccionada: ${indexResposta} de la pregunta ${indexPreguntaActual}`);
}

function iniciarJoc() {
    let numPreguntes = document.getElementById("num-preguntes").value;
    let nomUsuari = document.getElementById("nom-usuari").value;

    if (numPreguntes && nomUsuari) {
        // Primero, ejecutar la migración
        fetch('/decero/back/runMigration.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Migración completada:', data.message);
                    // Continuar con el inicio del juego
                    iniciarJocDespuesDeMigracion(numPreguntes, nomUsuari);
                } else {
                    console.error('Error en la migración:', data.message);
                    alert("Hi ha hagut un error en preparar el joc. Si us plau, torna-ho a provar.");
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Hi ha hagut un error en preparar el joc. Si us plau, torna-ho a provar.");
            });
    } else {
        alert("Has d'introduir el nombre de preguntes i el nom d'usuari");
    }
}

function iniciarJocDespuesDeMigracion(numPreguntes, nomUsuari) {
    // El resto de tu código para iniciar el juego...
    fetch('http://localhost/decero/back/iniciarQuiz.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `num_preguntes=${numPreguntes}&nom_usuari=${encodeURIComponent(nomUsuari)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Quiz iniciat correctament');
            continuarIniciarJoc(numPreguntes, nomUsuari);
        } else {
            console.error('Error al iniciar el quiz:', data.message);
            alert("Hi ha hagut un error en iniciar el joc. Si us plau, torna-ho a provar.");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Hi ha hagut un error en iniciar el joc. Si us plau, torna-ho a provar.");
    });
}

function continuarIniciarJoc(numPreguntes, nomUsuari) {
    let mostrarUsuari = document.getElementById("mostrar-usuari");
    
    //funcions
    guardarNomUsuari(nomUsuari);
    obtenirPreguntes(parseInt(numPreguntes));
    mostrarUsuari.innerHTML = `Benvingut usuari: ${nomUsuari}`;
    reiniciarCronometro();

    //dom
    const divJoc = document.getElementById("joc");
    const divPaginaInicial = document.getElementById("pagina-inicial");
    let preguntaSeguentButton = document.getElementById("pregunta-seguent");
    let preguntaAnteriorButton = document.getElementById("pregunta-anterior");

    preguntesTotals.innerHTML = numPreguntes;

    preguntaSeguentButton.addEventListener("click", preguntaSeguent);
    preguntaAnteriorButton.addEventListener("click", preguntaAnterior);

    //estils
    divJoc.style.display = "block";
    divPaginaInicial.style.display = "none";
    veureResultatsButton.style.display = "none";
}

//Cronometro
function actualizarDisplay() {
    const minutosFormateados = minutos.toString().padStart(2, '0');
    const segundosFormateados = segundos.toString().padStart(2, '0');
    document.getElementById('contador').textContent = `${minutosFormateados}:${segundosFormateados}`;
}

function iniciarCronometro() {
    // Limpiar cualquier intervalo existente antes de iniciar uno nuevo
    if (intervalId) {
        clearInterval(intervalId);
    }

    // Reiniciar el tiempo
    segundos = 0;
    minutos = 0;
    actualizarDisplay();

    intervalId = setInterval(function() {
        segundos++;
        if (segundos === 60) {
            segundos = 0;
            minutos++;
        }
        actualizarDisplay();
    }, 1000);
}

function reiniciarCronometro() {
    iniciarCronometro();
}


// Preguntes
function preguntaSeguent() {
    if (indexPreguntaActual < preguntesQuiz.length - 1) {
        indexPreguntaActual++;
        mostrarPregunta();
        actualitzarNavegacio();
    } else {
        mostrarResultats();
    }
}

function preguntaAnterior() {
    if (indexPreguntaActual > 0) {
        indexPreguntaActual--;
        mostrarPregunta();
        actualitzarNavegacio();
    }
}

function actualitzarNavegacio() {
    const buttonAnterior = document.getElementById("pregunta-anterior");
    const buttonSeguent = document.getElementById("pregunta-seguent");

    buttonAnterior.disabled = indexPreguntaActual === 0;
    buttonSeguent.textContent = indexPreguntaActual === preguntesQuiz.length - 1 ? "Finalitzar" : "Següent";
}

function tornarInici() {
    const divJoc = document.getElementById("joc");
    const divPaginaInicial = document.getElementById("pagina-inicial");
    const divResultats = document.getElementById("resultat-final");
    indexPreguntaActual = 0;
    preguntesQuiz = [];
    document.getElementById("pregunta-actual").textContent = "1";
    let seguentPreguntaButton = document.getElementById("pregunta-seguent");
    seguentPreguntaButton.textContent = "Següent";
    divJoc.style.display = "none";
    divPaginaInicial.style.display = "block";
    divResultats.style.display = "none";
    
    if (intervalId) {
        clearInterval(intervalId);
    }
    document.getElementById('contador').textContent = "00:00";
}

function mostrarResultats() {
    const divJoc = document.getElementById("joc");
    const divResultats = document.getElementById("resultat-final");
    const tornarIniciButton = document.getElementById("tornar-inici");
    const resultatElement = document.getElementById("resultat"); // Asegúrate de que este elemento existe en tu HTML

    divJoc.style.display = "none";
    divResultats.style.display = "block";
    tornarIniciButton.addEventListener("click", tornarInici);

    // Llamar a finalitza.php para obtener los resultados
    fetch('http://localhost/decero/back/finalitza.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (resultatElement) {
                resultatElement.innerHTML = `
                    <p>Has encertat ${data.respostesCorrectes} de ${data.totalPreguntes} preguntes.</p>
                    <p>Percentatge d'encert: ${data.percentatge.toFixed(2)}%</p>
                `;
            }
            
            const correctesElement = document.getElementById("correctes");
            const totalPreguntesElement = document.getElementById("total-preguntes");
            
            if (correctesElement) correctesElement.textContent = data.respostesCorrectes;
            if (totalPreguntesElement) totalPreguntesElement.textContent = data.totalPreguntes;
        } else {
            if (resultatElement) {
                resultatElement.innerHTML = `<p>${data.message || 'No s\'han pogut obtenir els resultats.'}</p>`;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (resultatElement) {
            resultatElement.innerHTML = '<p>Hi ha hagut un error en obtenir els resultats.</p>';
        }
    });
}

veureResultatsButton.addEventListener("click", mostrarResultats);

// Admin panel
document.getElementById('admin-login').addEventListener('click', cargarPanelAdmin);

function cargarPanelAdmin() {
    fetch('/decero/back/dashboard/admin_login.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('admin-panel').innerHTML = data;
            document.getElementById('pagina-inicial').style.display = 'none';
            document.getElementById('admin-panel').style.display = 'block';
            
            // Añadir listener al formulario de login
            const loginForm = document.getElementById('admin-login-form');
            if (loginForm) {
                loginForm.addEventListener('submit', handleAdminLogin);
            }
        })
        .catch(error => console.error('Error:', error));
}

// Asegúrate de llamar a esta función después de un inicio de sesión exitoso
function handleAdminLogin(event) {
    event.preventDefault();
    const formData = new FormData(event.target);

    fetch('/decero/back/dashboard/admin_login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            actualizarPanelAdmin();
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Ha ocurrido un error en el servidor', 'error');
    });
}

function handleAdminAction(e) {
    e.preventDefault();
    const action = e.target.getAttribute('data-action');
    switch(action) {
        case 'listarPreguntes':
            listarPreguntes();
            break;
        case 'afegirPregunta':
            mostrarFormularioAgregarPregunta();
            break;
    }
}

function cargarDashboardAdmin() {
    fetch('/decero/back/dashboard/admin_dashboard.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('admin-panel').innerHTML = data;
            // Llamar a listarPreguntes automáticamente al cargar el dashboard
            listarPreguntes();
        })
        .catch(error => console.error('Error:', error));
}

function listarPreguntes() {
    fetch('/decero/back/dashboard/getPreguntes.php')
        .then(response => response.json())
        .then(data => {
            let html = '<h3>Llista de Preguntes</h3>';
            if (Array.isArray(data) && data.length > 0) {
                html += '<ul>';
                data.forEach(pregunta => {
                    html += `<li>${pregunta.pregunta} 
                             <button onclick="editarPregunta(${pregunta.id})">Editar</button>
                             <button onclick="eliminarPregunta(${pregunta.id})">Eliminar</button></li>`;
                });
                html += '</ul>';
            } else {
                html += '<p>No hi ha preguntes disponibles.</p>';
            }
            document.getElementById('contenido-admin').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('contenido-admin').innerHTML = '<p>Error al carregar les preguntes.</p>';
        });
}

function mostrarFormularioAgregarPregunta() {
    const html = `
        <h3>Afegir Nova Pregunta</h3>
        <form id="form-agregar-pregunta" enctype="multipart/form-data">
            <input type="text" name="pregunta" placeholder="Pregunta" required>
            <div id="respuestas-container">
                ${[1, 2, 3, 4].map(i => `
                    <div>
                        <input type="text" name="resposta${i}" placeholder="Resposta ${i}" required>
                        <input type="file" name="imatge${i}" accept="image/*">
                        <input type="radio" name="correcta" value="${i-1}" required> Correcta
                    </div>
                `).join('')}
            </div>
            <button type="submit">Afegir Pregunta</button>
        </form>
    `;
    document.getElementById('contenido-admin').innerHTML = html;
    document.getElementById('form-agregar-pregunta').addEventListener('submit', agregarPregunta);
}

function agregarPregunta(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    fetch('/decero/back/dashboard/agregarPregunta.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Èxit', 'Pregunta afegida correctament', 'success');
            listarPreguntes(); // Actualizar la lista de preguntas
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Hi ha hagut un error en afegir la pregunta', 'error');
    });
}

function editarPregunta(id) {
    fetch(`/decero/back/dashboard/getPregunta.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                Swal.fire('Error', data.error, 'error');
                return;
            }
            mostrarFormularioEditarPregunta(data);
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudo cargar la pregunta', 'error');
        });
}


function mostrarFormularioEditarPregunta(pregunta) {
    let html = `
        <h3>Editar Pregunta</h3>
        <form id="form-editar-pregunta">
            <input type="hidden" name="id" value="${pregunta.id}">
            <input type="text" name="pregunta" value="${pregunta.pregunta}" required>
    `;
    pregunta.respostes.forEach((resposta, index) => {
        html += `
            <div>
                <input type="text" name="resposta${index + 1}" value="${resposta.resposta}" required>
                <input type="radio" name="correcta" value="${index}" ${resposta.correcta ? 'checked' : ''}> Correcta
                <input type="hidden" name="resposta${index + 1}_id" value="${resposta.id}">
                <img src="${resposta.imatge}" alt="Imatge resposta ${index + 1}" style="max-width: 100px;">
                <input type="file" name="imatge${index + 1}" accept="image/*">
            </div>
        `;
    });
    html += `
            <button type="submit">Actualitzar Pregunta</button>
        </form>
    `;
    document.getElementById('contenido-admin').innerHTML = html;
    document.getElementById('form-editar-pregunta').addEventListener('submit', actualizarPregunta);
}

function actualizarPregunta(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    fetch('/decero/back/dashboard/actualizarPregunta.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Èxit', 'Pregunta actualitzada correctament', 'success');
            listarPreguntes();
        } else {
            console.error('Error al actualizar la pregunta:', data.message);
            Swal.fire('Error', `Error al actualitzar la pregunta: ${data.message}`, 'error');
        }
    })
    .catch(error => {
        console.error('Error en la solicitud:', error);
        Swal.fire('Error', 'Error en la solicitud al servidor', 'error');
    });
}

function eliminarPregunta(id) {
    Swal.fire({
        title: 'Estàs segur?',
        text: "Aquesta acció no es pot desfer!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, elimina-la!',
        cancelButtonText: 'Cancel·la'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/decero/back/dashboard/eliminarPregunta.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Eliminada!', 'La pregunta ha estat eliminada.', 'success');
                    listarPreguntes();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
}

function actualizarPanelAdmin() {
    const adminPanel = document.getElementById('admin-panel');
    
    // Crear el contenido del panel de administración
    const panelContent = `
        <h2>Panel d'Administració</h2>
        <nav>
            <ul>
                <li><button onclick="listarPreguntes()">Llistar Preguntes</button></li>
                <li><button onclick="mostrarFormularioAgregarPregunta()">Afegir Nova Pregunta</button></li>
            </ul>
        </nav>
        <div id="contenido-admin"></div>
    `;
    
    // Actualizar el contenido del panel
    adminPanel.innerHTML = panelContent;
    
    // Mostrar el panel de administración y ocultar otros elementos
    adminPanel.style.display = 'block';
    document.getElementById('pagina-inicial').style.display = 'none';
    document.getElementById('joc').style.display = 'none';
    
    // Cargar la lista de preguntas por defecto
    listarPreguntes();
}

// Función para volver a la página inicial desde el panel de administración
function volverAInicio() {
    document.getElementById('admin-panel').style.display = 'none';
    document.getElementById('pagina-inicial').style.display = 'block';
}

// No olvides agregar esta función a tu botón de "Volver" en el panel de administración
function agregarBotonVolver() {
    const botonVolver = document.createElement('button');
    botonVolver.textContent = 'Tornar a l\'inici';
    botonVolver.onclick = volverAInicio;
    document.getElementById('admin-panel').appendChild(botonVolver);
}
