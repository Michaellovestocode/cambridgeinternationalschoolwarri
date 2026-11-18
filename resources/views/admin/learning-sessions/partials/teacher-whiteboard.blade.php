@php
    $boardId = $boardId ?? 'teacher-whiteboard-' . uniqid();
    $boardName = $boardName ?? 'drawing';
    $boardValue = $boardValue ?? '';
    $boardLabel = $boardLabel ?? 'Optional teacher writing pad';
@endphp
<div class="{{ $boardClass ?? '' }} rounded-2xl border border-cyan-200 bg-cyan-50 p-4" data-teacher-whiteboard>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="font-bold text-cyan-950">{{ $boardLabel }}</p>
            <p class="text-xs text-cyan-800">Write or draw with a mouse, finger, or stylus. Your work is saved as a white-background image with this question.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" data-board-undo class="rounded-lg bg-white px-3 py-2 text-xs font-bold text-gray-700">Undo</button>
            <button type="button" data-board-redo class="rounded-lg bg-white px-3 py-2 text-xs font-bold text-gray-700">Redo</button>
            <button type="button" data-board-eraser class="rounded-lg bg-white px-3 py-2 text-xs font-bold text-gray-700">Eraser</button>
            <button type="button" data-board-clear class="rounded-lg bg-white px-3 py-2 text-xs font-bold text-rose-700">Clear</button>
        </div>
    </div>
    <div class="mt-3 overflow-hidden rounded-xl border border-gray-200 bg-white">
        <canvas id="{{ $boardId }}" data-board-canvas width="1000" height="620" class="block h-auto w-full touch-none bg-white"></canvas>
    </div>
    <input type="hidden" name="{{ $boardName }}" value="{{ $boardValue }}" data-board-input>
</div>

@once
<script>
    window.initTeacherWhiteboards = function (root = document) {
        root.querySelectorAll('[data-teacher-whiteboard]:not([data-board-ready])').forEach(function (board) {
            board.dataset.boardReady = '1';
            const canvas = board.querySelector('[data-board-canvas]');
            const hidden = board.querySelector('[data-board-input]');
            const context = canvas?.getContext('2d');
            if (!canvas || !hidden || !context) return;

            let drawing = false;
            let erasing = false;
            let lastPoint = null;
            const history = [];
            const future = [];
            context.lineCap = 'round';
            context.lineJoin = 'round';
            context.lineWidth = 5;
            context.strokeStyle = '#111827';

            function snapshot() { return canvas.toDataURL('image/png'); }
            function remember() {
                history.push(snapshot());
                if (history.length > 30) history.shift();
            }
            function saveImage() {
                const output = document.createElement('canvas');
                output.width = canvas.width;
                output.height = canvas.height;
                const outputContext = output.getContext('2d');
                outputContext.fillStyle = '#fff';
                outputContext.fillRect(0, 0, output.width, output.height);
                outputContext.drawImage(canvas, 0, 0);
                hidden.value = output.toDataURL('image/jpeg', 0.82);
            }
            function restore(imageData) {
                const image = new Image();
                image.onload = function () {
                    context.clearRect(0, 0, canvas.width, canvas.height);
                    context.drawImage(image, 0, 0, canvas.width, canvas.height);
                    saveImage();
                };
                image.src = imageData;
            }
            board.restoreDrawing = restore;
            function point(event) {
                const bounds = canvas.getBoundingClientRect();
                return { x: (event.clientX - bounds.left) * canvas.width / bounds.width, y: (event.clientY - bounds.top) * canvas.height / bounds.height };
            }

            if (hidden.value) restore(hidden.value);
            canvas.addEventListener('pointerdown', function (event) {
                event.preventDefault();
                canvas.setPointerCapture(event.pointerId);
                remember();
                future.length = 0;
                drawing = true;
                lastPoint = point(event);
                context.globalCompositeOperation = erasing ? 'destination-out' : 'source-over';
                context.lineWidth = erasing ? 28 : 5;
                context.beginPath();
                context.moveTo(lastPoint.x, lastPoint.y);
                context.lineTo(lastPoint.x + 0.1, lastPoint.y + 0.1);
                context.stroke();
            });
            canvas.addEventListener('pointermove', function (event) {
                if (!drawing) return;
                const nextPoint = point(event);
                context.globalCompositeOperation = erasing ? 'destination-out' : 'source-over';
                context.lineWidth = erasing ? 28 : 5;
                context.beginPath();
                context.moveTo(lastPoint.x, lastPoint.y);
                context.lineTo(nextPoint.x, nextPoint.y);
                context.stroke();
                lastPoint = nextPoint;
            });
            ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (eventName) {
                canvas.addEventListener(eventName, function () {
                    if (!drawing) return;
                    drawing = false;
                    context.globalCompositeOperation = 'source-over';
                    saveImage();
                });
            });
            board.querySelector('[data-board-eraser]')?.addEventListener('click', function () {
                erasing = !erasing;
                this.classList.toggle('bg-cyan-200', erasing);
                this.setAttribute('aria-pressed', String(erasing));
            });
            board.querySelector('[data-board-clear]')?.addEventListener('click', function () {
                remember();
                future.length = 0;
                context.clearRect(0, 0, canvas.width, canvas.height);
                hidden.value = '';
            });
            board.querySelector('[data-board-undo]')?.addEventListener('click', function () {
                if (!history.length) return;
                future.push(snapshot());
                restore(history.pop());
            });
            board.querySelector('[data-board-redo]')?.addEventListener('click', function () {
                if (!future.length) return;
                history.push(snapshot());
                restore(future.pop());
            });
        });
    };
    document.addEventListener('DOMContentLoaded', function () { window.initTeacherWhiteboards(); });
</script>
@endonce
