<form action="/"
      class="absolute h-[50px] w-[260px] bg-white rounded-[60px] border border-solid border-theme6 right-[60px] z-10 flex items-center"
      x-transition
      x-data="{items: [], query: ''}"
      x-show="showSearch">
	<input name="s"
	       placeholder="Пошук"
	       x-model="query"
	       x-on:input="Alpine.store('FS').liveSearch(query).then(r=>items=r.data.items   )"
	       value="<?php echo get_search_query() ?>" type="text"
	       class="absolute w-full h-full outline-none text-black pl-3 pr-10 rounded-[60px]">
	<button class="absolute right-[13px]">
		<img src="/wp-content/themes/roov/img/icons/search.svg" alt=""></button>

	<ul class="absolute left-0 top-[100%] mt-1 bg-white p-1.5 overflow-auto max-h-[260px] flex flex-col gap-1.5"
	    x-show="items.length>0" x-transition>
		<template x-for="item in items">
			<li class="wp-full flex flex-col">
				<a :href="item.link" class="flex justify-between gap-2 items-center text-black w-full">
					<img :src="item.thumbnail" width="30" :alt="item.title">
					<div>
						<span x-text="item.title" class="text-sm text-black"></span>
						<div x-text="item.price +' '+ item.currency" class="text-black/50"></div>
					</div>
				</a>
			</li>
		</template>
	</ul>
</form>