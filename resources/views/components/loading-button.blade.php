@props([
    'type' => 'button',
    'loadingTarget' => null,
])

@php
    $resolvedLoadingTarget = $loadingTarget ?? $attributes->get('wire:target') ?? $attributes->get('wire:click');
    $tracksLivewire = filled($resolvedLoadingTarget);
    $tracksSubmit = $type === 'submit' && ! $tracksLivewire;
    $buttonAttributes = $attributes
        ->except(['wire:target'])
        ->class(['loading-button']);

    $submitTracking = $tracksSubmit ? 'true' : 'false';
@endphp

<button
    type="{{ $type }}"
    x-data="{
        isSubmitting: false,
        init() {
            if (! {{ $submitTracking }}) {
                return;
            }

            const formId = this.$el.getAttribute('form');
            const form = formId ? document.getElementById(formId) : this.$el.form;

            if (!form) {
                return;
            }

            form.addEventListener('submit', (event) => {
                if (event.submitter !== this.$el) {
                    return;
                }

                requestAnimationFrame(() => {
                    if (event.defaultPrevented || this.$el.disabled) {
                        return;
                    }

                    this.isSubmitting = true;
                    this.$el.disabled = true;
                });
            });
        }
    }"
    @if ($tracksLivewire)
        wire:loading.attr="disabled"
        wire:target="{{ $resolvedLoadingTarget }}"
    @endif
    {{ $buttonAttributes }}
>
    <span
        class="loading-button__label"
        :class="{ 'loading-button__label--hidden': isSubmitting }"
        @if ($tracksLivewire)
            wire:loading.class="loading-button__label--hidden"
            wire:target="{{ $resolvedLoadingTarget }}"
        @endif
    >{{ $slot }}</span>

    <span
        class="loading-button__spinner"
        :class="{ 'loading-button__spinner--visible': isSubmitting }"
        @if ($tracksLivewire)
            wire:loading.class="loading-button__spinner--visible"
            wire:target="{{ $resolvedLoadingTarget }}"
        @endif
        aria-hidden="true"
    >
        <span class="loading-button__spinner-ring"></span>
    </span>
</button>