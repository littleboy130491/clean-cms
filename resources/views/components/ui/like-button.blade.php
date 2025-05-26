@props([
    'content',
    'lang',
    'contentType',
    'showCount' => true,
    'size' => 'md', // 'sm', 'md', 'lg'
    'variant' => 'default', // 'default', 'minimal', 'outline'
])

@php
    $cookieName = "liked_content_{$content->id}";
    $hasLiked = request()->cookie($cookieName) === 'true';
    $likesCount = $content->page_likes ?? 0;

    $sizeClasses = match ($size) {
        'sm' => 'text-sm px-2 py-1',
        'lg' => 'text-lg px-4 py-3',
        default => 'text-base px-3 py-2',
    };

    $variantClasses = match ($variant) {
        'minimal' => 'bg-transparent hover:bg-gray-100 text-gray-600',
        'outline' => 'border border-gray-300 bg-white hover:bg-gray-50 text-gray-700',
        default => 'bg-gray-100 hover:bg-gray-200 text-gray-700',
    };

    $likeUrl = route('cms.content.like', [
        'lang' => $lang,
        'content_type_key' => $contentType,
        'content_slug' => $content->slug,
    ]);
@endphp

<button type="button" data-like-url="{{ $likeUrl }}" data-content-id="{{ $content->id }}"
    class="like-button inline-flex items-center gap-2 rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 {{ $sizeClasses }} {{ $variantClasses }} {{ $hasLiked ? 'liked' : '' }}"
    {{ $attributes }}>
    <!-- Heart Icon -->
    <svg class="like-icon w-5 h-5 transition-all duration-200" fill="{{ $hasLiked ? 'currentColor' : 'none' }}"
        stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
    </svg>

    @if ($showCount)
        <span class="like-count">{{ number_format($likesCount) }}</span>
    @endif

    <span class="like-text">
        <span class="like-text-default">{{ $hasLiked ? 'Liked' : 'Like' }}</span>
        <span class="like-text-loading hidden">...</span>
    </span>
</button>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle like button clicks
                document.querySelectorAll('.like-button').forEach(button => {
                    button.addEventListener('click', async function(e) {
                        e.preventDefault();

                        const url = this.dataset.likeUrl;
                        const contentId = this.dataset.contentId;
                        const icon = this.querySelector('.like-icon');
                        const countElement = this.querySelector('.like-count');
                        const textDefault = this.querySelector('.like-text-default');
                        const textLoading = this.querySelector('.like-text-loading');

                        // Show loading state
                        this.disabled = true;
                        textDefault.classList.add('hidden');
                        textLoading.classList.remove('hidden');

                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]')?.getAttribute(
                                        'content') || ''
                                },
                                credentials: 'same-origin'
                            });

                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }

                            const data = await response.json();

                            if (data.success) {
                                // Update UI based on like status
                                if (data.liked) {
                                    this.classList.add('liked');
                                    icon.setAttribute('fill', 'currentColor');
                                    textDefault.textContent = 'Liked';
                                    this.classList.add('text-red-600');
                                } else {
                                    this.classList.remove('liked');
                                    icon.setAttribute('fill', 'none');
                                    textDefault.textContent = 'Like';
                                    this.classList.remove('text-red-600');
                                }

                                // Update count
                                if (countElement) {
                                    countElement.textContent = new Intl.NumberFormat().format(data
                                        .likes_count);
                                }

                                // Add animation effect
                                icon.classList.add('scale-125');
                                setTimeout(() => {
                                    icon.classList.remove('scale-125');
                                }, 200);
                            }
                        } catch (error) {
                            console.error('Error toggling like:', error);
                            // You could show an error message here
                        } finally {
                            // Hide loading state
                            this.disabled = false;
                            textDefault.classList.remove('hidden');
                            textLoading.classList.add('hidden');
                        }
                    });
                });
            });
        </script>
    @endpush
@endonce
