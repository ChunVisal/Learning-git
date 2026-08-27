<div class="relative flex-1">
    <input type="text" x-model="searchQuery" placeholder="{{ $placeholder ?? 'Search...' }}">
    <p class="absolute right-2 top-1/2" style="cursor: pointer" x-show="searchQuery.length > 0" @click="searchQuery = ''">
        x
    </p>
</div>
