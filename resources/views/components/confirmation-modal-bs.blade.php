@props([
    'id',
    'title' => 'Are you sure?',
    'message' => 'Are you sure you would like to do this?',
    'icon' => 'warning', // 'warning', 'trash'
    'confirmText' => 'Confirm',
    'confirmButtonClass' => 'btn-danger',
    'formId' => '',
    'method' => 'POST'
])

{{--
    Bootstrap counterpart of x-confirmation-modal.

    Keeps the original JS contract exactly: callers open it with
    classList.remove('jb-hidden') and close it via [data-modal-close]. It is
    deliberately not a Bootstrap .modal, which would require every call site
    to switch to Bootstrap's JS API.
--}}
<div id="{{ $id }}" class="jb-modal jb-hidden">
    <div class="jb-modal-backdrop" data-modal-close="{{ $id }}"></div>

    <div class="jb-modal-panel">
        <button type="button" class="jb-modal-close" data-modal-close="{{ $id }}">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="jb-modal-icon">
            @if ($icon === 'trash')
                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            @else
                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            @endif
        </div>

        <h3 class="jb-modal-title" id="{{ $id }}-title">{!! $title !!}</h3>
        <p class="jb-modal-message" id="{{ $id }}-message">{!! $message !!}</p>

        <div class="d-flex align-items-center gap-3">
            <button type="button" class="btn btn-secondary flex-fill" data-modal-close="{{ $id }}">
                Cancel
            </button>

            @if ($formId)
                <form id="{{ $formId }}" method="POST" action="" class="flex-fill">
                    @csrf
                    @method($method)
                    <button type="submit" class="btn w-100 {{ $confirmButtonClass }}">
                        {{ $confirmText }}
                    </button>
                </form>
            @else
                <button type="button" id="{{ $id }}-confirm-btn" class="btn flex-fill {{ $confirmButtonClass }}">
                    {{ $confirmText }}
                </button>
            @endif
        </div>
    </div>
</div>
