<div class="chat-panel" id="chatPanel" aria-label="ChatBot especializado ¿Y qué hago?" aria-live="polite">
    <div class="chat-header">
        <div>
            <div class="chat-title">Asistente Electoral</div>
            <div class="chat-subtitle">Solo respondo sobre esta plataforma y plazos/documentos electorales.</div>
        </div>
        <button class="btn btn-secondary" type="button" id="btnCerrarChat" style="padding-inline: 10px; font-size: 12px;">
            Cerrar
        </button>
    </div>
    <div class="chat-body" id="chatBody">
        <div class="chat-message bot">
            Hola, soy el asistente de <strong>¿Y qué hago?</strong>.<br>
            Puedo orientarte sobre plazos, tipos de documentos de esta herramienta, rutas institucionales del IEE Chihuahua
            y cómo usar la plataforma. No doy asesoría legal formal.
        </div>
    </div>
    <form class="chat-input" id="chatForm">
        <input type="text" id="chatInput" placeholder="Escribe tu pregunta..." autocomplete="off" aria-label="Escribe tu pregunta al asistente">
        <button class="btn btn-primary" type="submit" aria-label="Enviar pregunta al asistente">Enviar</button>
    </form>
</div>

<div class="chat-fab">
    <button class="chat-bubble" type="button" id="btnAbrirChat" aria-label="Abrir chat de ayuda">
        ?
    </button>
</div>

@push('scripts')
<script>
    (function () {
        const chatPanel = document.getElementById('chatPanel');
        const chatBody = document.getElementById('chatBody');
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const openBtn = document.getElementById('btnAbrirChat');
        const closeBtn = document.getElementById('btnCerrarChat');

        function abrirChat() {
            chatPanel.setAttribute('data-open', 'true');
            setTimeout(() => chatInput && chatInput.focus(), 50);
        }

        function cerrarChat() {
            chatPanel.setAttribute('data-open', 'false');
        }

        function agregarMensaje(texto, tipo) {
            const div = document.createElement('div');
            div.className = 'chat-message ' + tipo;
            div.innerHTML = texto;
            chatBody.appendChild(div);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function responderChat(preguntaRaw) {
            const pregunta = preguntaRaw.toLowerCase();

            const fueraDeAlcance = ['futbol', 'clima', 'whatsapp', 'facebook', 'tiktok', 'receta', 'medico', 'salud'];
            if (fueraDeAlcance.some(p => pregunta.includes(p))) {
                return 'Solo puedo responder sobre plazos electorales, tipos de documentos que ves aquí, rutas institucionales del IEE Chihuahua y el uso de la plataforma ¿Y qué hago?. Para otros temas, te sugiero buscar una fuente especializada.';
            }

            if (pregunta.includes('plazo') && (pregunta.includes('acuerdo de requerimiento') || pregunta.includes('requerimiento'))) {
                return 'En un acuerdo de requerimiento, el plazo suele ser corto (por ejemplo, 3 días hábiles) y normalmente empieza a contar al día siguiente de la notificación. Revisa tu documento donde indica “dentro de X días hábiles”.';
            }

            if (pregunta.includes('3 dias') || pregunta.includes('3 días')) {
                return 'Un plazo de 3 días hábiles se cuenta sin fines de semana ni días inhábiles oficiales. Si ya te notificaron, actúa cuanto antes y confirma directamente con el IEE Chihuahua si aún estás en tiempo.';
            }

            if (pregunta.includes('tipo de documento') || pregunta.includes('tipos de documento')) {
                return 'Esta herramienta trabaja con cuatro tipos de documentos: 1) Acuerdo de requerimiento, 2) Notificación de resolución, 3) Resolución de medio de impugnación y 4) Acuerdo de desechamiento o improcedencia.';
            }

            if (pregunta.includes('iee') || pregunta.includes('chihuahua')) {
                return 'El IEE Chihuahua es la autoridad administrativa electoral local. Es un buen primer contacto para dudas sobre acuerdos, notificaciones y orientación sobre medios de impugnación en el ámbito local.';
            }

            if (pregunta.includes('como funciona') || pregunta.includes('funcionamiento') || pregunta.includes('plataforma')) {
                return '¿Y qué hago? ofrece: 1) Traductor de documentos (pagina “Traductor”), 2) Orientación guiada paso a paso, y 3) Modo urgencia para casos donde el plazo ya está corriendo. Cada módulo te da una ruta clara y orientativa.';
            }

            if (pregunta.includes('medio de impugnacion') || pregunta.includes('medio de impugnación')) {
                return 'Los medios de impugnación permiten inconformarte con decisiones electorales. Sus plazos son muy estrictos y suelen contarse desde la notificación de la resolución. A nivel normativo se apoyan en la LGSMIME, LGIPE y la legislación electoral local.';
            }

            return 'Con lo que me escribes no puedo darte una respuesta exacta. Recuerda que solo doy orientación general sobre plazos, documentos y rutas institucionales. Te recomiendo acudir directamente al IEE Chihuahua o a la autoridad que emitió tu documento para una aclaración formal.';
        }

        if (openBtn) openBtn.addEventListener('click', abrirChat);
        if (closeBtn) closeBtn.addEventListener('click', cerrarChat);

        if (chatForm) {
            chatForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const texto = chatInput.value.trim();
                if (!texto) return;
                agregarMensaje(texto, 'user');
                const respuesta = responderChat(texto);
                setTimeout(() => {
                    agregarMensaje(respuesta, 'bot');
                }, 120);
                chatInput.value = '';
            });
        }
    })();
</script>
@endpush
