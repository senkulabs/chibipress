<div x-data="{ activeItem: 1}">
    <div class="border rounded-lg overflow-hidden">
        <button
            class="w-full px-6 py-4 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-inset transition-colors duration-200"
            @click="activeItem = activeItem === 1 ? null : 1" :aria-expanded="activeItem === 1">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="font-medium text-gray-800">{{ $title }}</span>
                </div>
                <svg class="w-5 h-5 transform transition-transform duration-200"
                    :class="{ 'rotate-180': activeItem === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                    </path>
                </svg>
            </div>
        </button>
        <div x-show="activeItem === 1" x-collapse x-cloak>
            <div class="px-6 py-4 bg-white border-t border-gray-200">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
