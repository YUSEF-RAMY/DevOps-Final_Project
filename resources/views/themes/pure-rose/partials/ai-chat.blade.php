<div id="ai-chat-drawer">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="border-color: rgba(197,160,89,.25) !important;">
        <h6 class="mb-0 font-display text-gold"><i class="bi bi-flower1 me-2"></i>Floral Assistant</h6>
        <button class="btn btn-sm text-cream" id="ai-chat-close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="flex-grow-1 overflow-auto p-3" id="ai-chat-messages">
        <div class="ai-bubble">
            <small class="text-gold">PURE ROSE</small>
            <p class="mb-0 small">Welcome. Tell me the occasion, palette, or mood — I'll suggest bouquets from our live catalog.</p>
        </div>
    </div>
    <div class="p-3 border-top" style="border-color: rgba(197,160,89,.25) !important;">
        <form id="ai-chat-form" class="d-flex gap-2">
            <input type="text" class="form-control form-control-sm" id="ai-chat-input" placeholder="Ask about roses, birthdays, gifts..." autocomplete="off">
            <button type="submit" class="btn btn-gold-solid btn-sm">Send</button>
        </form>
    </div>
</div>
<button class="btn btn-gold-solid position-fixed" style="bottom:24px;right:24px;z-index:9998;border-radius:50%;width:56px;height:56px;" id="ai-chat-open" title="Floral Assistant">
    <i class="bi bi-chat-dots fs-5"></i>
</button>

@push('scripts')
<script>
(function(){
    const drawer = document.getElementById('ai-chat-drawer');
    const messages = document.getElementById('ai-chat-messages');
    const form = document.getElementById('ai-chat-form');
    const input = document.getElementById('ai-chat-input');
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    document.getElementById('ai-chat-open')?.addEventListener('click', () => drawer.classList.add('open'));
    document.getElementById('ai-chat-close')?.addEventListener('click', () => drawer.classList.remove('open'));

    function appendBubble(text, isUser, product) {
        const div = document.createElement('div');
        div.className = 'ai-bubble';
        if (isUser) div.style.background = 'rgba(255,255,255,.06)';
        let html = isUser ? `<p class="mb-0 small">${text}</p>` : `<small class="text-gold">PURE ROSE</small><p class="mb-0 small">${text}</p>`;
        if (product) {
            html += `<form action="{{ route('cart.add') }}" method="POST" class="mt-2">
                <input type="hidden" name="_token" value="${token}">
                <input type="hidden" name="product_id" value="${product.id}">
                <button class="btn btn-gold-solid btn-sm w-100">ADD TO CART — ${product.name}</button>
            </form>`;
        }
        div.innerHTML = html;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = input.value.trim();
        if (!msg) return;
        appendBubble(msg, true);
        input.value = '';
        try {
            const res = await fetch('/api/ai/consult', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ message: msg })
            });
            const data = await res.json();
            appendBubble(data.reply || 'Let me find the perfect bouquet for you.', false, data.product);
        } catch (err) {
            appendBubble('Our florist is momentarily unavailable. Please browse our signature collection.', false);
        }
    });
})();
</script>
@endpush
