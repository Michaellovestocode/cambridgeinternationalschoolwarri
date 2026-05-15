<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['value' => '', 'name' => 'question_text', 'placeholder' => 'Enter your question here']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['value' => '', 'name' => 'question_text', 'placeholder' => 'Enter your question here']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div x-data="richQuestionEditor(<?php echo \Illuminate\Support\Js::from($value)->toHtml() ?>)" x-init="init()" class="space-y-2">
    <input type="hidden" name="<?php echo e($name); ?>" x-ref="input" required>

    <div class="border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-green-500">
        <div class="bg-gray-50 border-b px-3 py-2 flex items-center gap-2">
            <button type="button"
                    @click="format('underline')"
                    class="px-3 py-1 rounded text-sm font-semibold underline text-gray-700 hover:bg-gray-200"
                    title="Underline selected text">
                U
            </button>
            <button type="button"
                    @click="format('bold')"
                    class="px-3 py-1 rounded text-sm font-bold text-gray-700 hover:bg-gray-200"
                    title="Bold selected text">
                B
            </button>
            <button type="button"
                    @click="format('italic')"
                    class="px-3 py-1 rounded text-sm italic text-gray-700 hover:bg-gray-200"
                    title="Italic selected text">
                I
            </button>
        </div>

        <div x-ref="editor"
             contenteditable="true"
             @input="sync()"
             @blur="sync()"
             @paste.prevent="paste($event)"
             class="w-full min-h-[7rem] px-4 py-3 bg-white text-gray-900 focus:outline-none"
             data-placeholder="<?php echo e($placeholder); ?>"></div>
    </div>

    <p class="text-xs text-gray-500">Use the underline button or paste underlined text from Word.</p>
</div>

<?php if (! $__env->hasRenderedOnce('55e27dee-9f44-4866-8c53-c4d0dbd133e5')): $__env->markAsRenderedOnce('55e27dee-9f44-4866-8c53-c4d0dbd133e5'); ?>
<style>
    [contenteditable][data-placeholder]:empty::before {
        content: attr(data-placeholder);
        color: #9ca3af;
        pointer-events: none;
    }
</style>

<?php $__env->startPush('scripts'); ?>
<script>
    function richQuestionEditor(initialValue) {
        return {
            value: initialValue || '',

            init() {
                this.$refs.editor.innerHTML = this.sanitizeHtml(this.value);
                this.sync();
            },

            format(command) {
                this.$refs.editor.focus();
                document.execCommand(command, false, null);
                this.sync();
            },

            sync() {
                this.$refs.input.value = this.sanitizeHtml(this.$refs.editor.innerHTML).trim();
            },

            paste(event) {
                const clipboard = event.clipboardData || window.clipboardData;
                const html = clipboard.getData('text/html');
                const text = clipboard.getData('text/plain');
                const content = html ? this.sanitizeHtml(html) : this.escapeText(text).replace(/\n/g, '<br>');

                document.execCommand('insertHTML', false, content);
                this.sync();
            },

            sanitizeHtml(html) {
                const template = document.createElement('template');
                template.innerHTML = html || '';
                template.content.querySelectorAll('style, script, meta, link, title, xml').forEach((node) => node.remove());

                return this.cleanChildren(template.content);
            },

            cleanChildren(parent) {
                return Array.from(parent.childNodes).map((node) => this.cleanNode(node)).join('');
            },

            cleanNode(node) {
                if (node.nodeType === Node.TEXT_NODE) {
                    return this.escapeText(node.textContent);
                }

                if (node.nodeType !== Node.ELEMENT_NODE) {
                    return '';
                }

                const tag = node.tagName.toLowerCase();

                if (['style', 'script', 'meta', 'link', 'title', 'xml'].includes(tag)) {
                    return '';
                }

                const children = this.cleanChildren(node);
                const style = (node.getAttribute('style') || '').toLowerCase();
                const isUnderlined = tag === 'u' || style.includes('text-decoration') && style.includes('underline');
                const isBold = ['b', 'strong'].includes(tag) || ['700', 'bold'].some((value) => style.includes(`font-weight: ${value}`) || style.includes(`font-weight:${value}`));
                const isItalic = ['i', 'em'].includes(tag) || style.includes('font-style: italic') || style.includes('font-style:italic');

                if (tag === 'br') {
                    return '<br>';
                }

                if (['p', 'div', 'ol', 'ul', 'li', 'sub', 'sup'].includes(tag)) {
                    return `<${tag}>${children}</${tag}>`;
                }

                let wrapped = children;
                if (isItalic) wrapped = `<em>${wrapped}</em>`;
                if (isBold) wrapped = `<strong>${wrapped}</strong>`;
                if (isUnderlined) wrapped = `<u>${wrapped}</u>`;

                return wrapped;
            },

            escapeText(text) {
                const div = document.createElement('div');
                div.textContent = text || '';
                return div.innerHTML;
            },
        };
    }
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\admin\exams\partials\rich-question-editor.blade.php ENDPATH**/ ?>