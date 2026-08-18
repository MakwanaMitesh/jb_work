@props([
    'id',
    'title' => 'Are you sure?',
    'message' => 'Are you sure you would like to do this?',
    'icon' => 'warning', // 'warning', 'trash'
    'confirmText' => 'Confirm',
    'confirmButtonClass' => 'bg-red-600 hover:bg-red-500 focus:ring-red-500/20 text-white',
    'formId' => '',
    'method' => 'POST'
])

<div id="{{ $id }}" class="fixed inset-0 z-50 overflow-y-auto hidden flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" data-modal-close="{{ $id }}"></div>

    <!-- Modal Content -->
    <div class="relative bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full shadow-2xl border border-slate-200/80 dark:border-slate-800 p-6 text-center transform transition-all z-10">
        <!-- Close Button (X) -->
        <button type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition" data-modal-close="{{ $id }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Icon -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-4 {{ $icon === 'trash' ? 'bg-red-50 dark:bg-red-950/20 text-red-600' : 'bg-red-50 dark:bg-red-950/20 text-red-600' }}">
            @if ($icon === 'trash')
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            @else
                <!-- Warning icon -->
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            @endif
        </div>

        <!-- Title & Subtitle -->
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2" id="{{ $id }}-title">{!! $title !!}</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 px-4" id="{{ $id }}-message">{!! $message !!}</p>

        <!-- Actions Footer Buttons -->
        <div class="flex items-center gap-3">
            <button type="button" class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm" data-modal-close="{{ $id }}">
                Cancel
            </button>
            @if ($formId)
                <form id="{{ $formId }}" method="POST" action="" class="flex-1">
                    @csrf
                    @method($method)
                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-sm transition shadow-sm {{ $confirmButtonClass }}">
                        {{ $confirmText }}
                    </button>
                </form>
            @else
                <button type="button" id="{{ $id }}-confirm-btn" class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-sm transition shadow-sm {{ $confirmButtonClass }}">
                    {{ $confirmText }}
                </button>
            @endif
        </div>
    </div>
</div>
