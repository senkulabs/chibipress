<div>
    <div class="border rounded-lg"
        x-data="richEditor(@entangle($attributes['wire:model']).live)" x-init="() => init($refs.editor)" wire:ignore
        {{ $attributes->whereDoesntStartWith('wire:model') }}>
        <div class="toolbar flex space-x-2 border-b p-2">
            <button type="button"
                @click="toggleBold()"
                :class="{ 'bg-zinc-800/10': isActive.bold }"
                class="inline-flex items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none h-8 text-sm rounded-md w-8 bg-zinc-800/5 hover:bg-zinc-800/10 dark:bg-white/10 dark:hover:bg-white/20 text-zinc-800 dark:text-white group-[]/button:border-r group-[]/button:last:border-r-0 group-[]/button:border-black group-[]/button:dark:border-zinc-900/25">
                <flux:icon.bold class="size-5" />
            </button>
            <button type="button" @click="toggleItalic()"
                :class="{ 'bg-zinc-800/10': isActive.italic }"
                class="relative inline-flex items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none h-8 text-sm rounded-md w-8 bg-zinc-800/5 hover:bg-zinc-800/10 dark:bg-white/10 dark:hover:bg-white/20 text-zinc-800 dark:text-white group-[]/button:border-r group-[]/button:last:border-r-0 group-[]/button:border-black group-[]/button:dark:border-zinc-900/25">
                <flux:icon.italic class="size-5" />
            </button>
            <button type="button" @click="toggleUnderline()"
                :class="{ 'bg-zinc-800/10': isActive.underline }"
                class="relative inline-flex items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none h-8 text-sm rounded-md w-8 bg-zinc-800/5 hover:bg-zinc-800/10 dark:bg-white/10 dark:hover:bg-white/20 text-zinc-800 dark:text-white group-[]/button:border-r group-[]/button:last:border-r-0 group-[]/button:border-black group-[]/button:dark:border-zinc-900/25">
                <flux:icon.underline class="size-5" />
            </button>
            <flux:separator vertical class="my-1" variant="subtle" />
            <button type="button"
                :class="{ 'bg-zinc-800/10': isActive.highlight }"
                class="relative inline-flex items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none h-8 text-sm rounded-md w-8 bg-zinc-800/5 hover:bg-zinc-800/10 dark:bg-white/10 dark:hover:bg-white/20 text-zinc-800 dark:text-white group-[]/button:border-r group-[]/button:last:border-r-0 group-[]/button:border-black group-[]/button:dark:border-zinc-900/25"
                @click="toggleHighlight()">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000"
                    viewBox="0 0 256 256" class="size-5">
                    <path
                        d="M253.66,106.34a8,8,0,0,0-11.32,0L192,156.69,107.31,72l50.35-50.34a8,8,0,1,0-11.32-11.32L96,60.69A16,16,0,0,0,93.18,79.5L72,100.69a16,16,0,0,0,0,22.62L76.69,128,18.34,186.34a8,8,0,0,0,3.13,13.25l72,24A7.88,7.88,0,0,0,96,224a8,8,0,0,0,5.66-2.34L136,187.31l4.69,4.69a16,16,0,0,0,22.62,0l21.19-21.18A16,16,0,0,0,203.31,168l50.35-50.34A8,8,0,0,0,253.66,106.34ZM93.84,206.85l-55-18.35L88,139.31,124.69,176ZM152,180.69,83.31,112,104,91.31,172.69,160Z">
                    </path>
                </svg>
            </button>
            <flux:separator vertical class="my-1" variant="subtle" />
            <button type="button"
                :class="{ 'bg-zinc-800/10': isActive.heading1 }"
                @click="toggleHeading({ level: 1 })"
                class="relative inline-flex items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none h-8 text-sm rounded-md w-8 bg-zinc-800/5 hover:bg-zinc-800/10 dark:bg-white/10 dark:hover:bg-white/20 text-zinc-800 dark:text-white group-[]/button:border-r group-[]/button:last:border-r-0 group-[]/button:border-black group-[]/button:dark:border-zinc-900/25"
                >
                <flux:icon.h1 class="size-5" />
            </button>
            <button type="button"
                :class="{ 'bg-zinc-800/10': isActive.heading2 }"
                @click="toggleHeading({ level: 2 })"
                class="relative inline-flex items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none h-8 text-sm rounded-md w-8 bg-zinc-800/5 hover:bg-zinc-800/10 dark:bg-white/10 dark:hover:bg-white/20 text-zinc-800 dark:text-white group-[]/button:border-r group-[]/button:last:border-r-0 group-[]/button:border-black group-[]/button:dark:border-zinc-900/25"
                >
                <flux:icon.h2 class="size-5" />
            </button>
            <button type="button"
                :class="{ 'bg-zinc-800/10': isActive.heading3 }"
                @click="toggleHeading({ level: 3 })"
                class="relative inline-flex items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none h-8 text-sm rounded-md w-8 bg-zinc-800/5 hover:bg-zinc-800/10 dark:bg-white/10 dark:hover:bg-white/20 text-zinc-800 dark:text-white group-[]/button:border-r group-[]/button:last:border-r-0 group-[]/button:border-black group-[]/button:dark:border-zinc-900/25"
                >
                <flux:icon.h3 class="size-5" />
            </button>
            <flux:separator vertical class="my-1" variant="subtle" />
            <button type="button"
                :class="{ 'bg-zinc-800/10': isActive.bulletList }"
                @click="toggleBulletList()"
                class="relative inline-flex items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none h-8 text-sm rounded-md w-8 bg-zinc-800/5 hover:bg-zinc-800/10 dark:bg-white/10 dark:hover:bg-white/20 text-zinc-800 dark:text-white group-[]/button:border-r group-[]/button:last:border-r-0 group-[]/button:border-black group-[]/button:dark:border-zinc-900/25"
                >
                <flux:icon.list-bullet class="size-5" />
            </button>
            <button type="button"
                :class="{ 'bg-zinc-800/10': isActive.orderedList }"
                @click="toggleOrderedList()"
                class="relative inline-flex items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none h-8 text-sm rounded-md w-8 bg-zinc-800/5 hover:bg-zinc-800/10 dark:bg-white/10 dark:hover:bg-white/20 text-zinc-800 dark:text-white group-[]/button:border-r group-[]/button:last:border-r-0 group-[]/button:border-black group-[]/button:dark:border-zinc-900/25"
                >
                <flux:icon.numbered-list class="size-5" />
            </button>
        </div>

        <div x-ref="editor" class="prose prose-zinc prose-sm dark:prose-invert prose-p:m-0 leading-relaxed py-2 px-3 mt-1 focus:!outline-none focus:ring-2 focus:!ring-zinc-600 focus:border-transparent">
        </div>
    </div>
</div>
