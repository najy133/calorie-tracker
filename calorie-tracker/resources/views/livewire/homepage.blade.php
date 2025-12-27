<div class="grid gap-6 md:grid-cols-2">
    <section class="rounded-xl border border-gray-100 bg-gradient-to-br from-indigo-50 via-white to-white p-5 shadow-sm">
        <header class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Today's Calories</h2>
            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700">Placeholder</span>
        </header>
        <p class="text-4xl font-bold text-indigo-600">{{$calories}}</p>
        <p class="mt-2 text-sm text-gray-600">Static value for now. Hook up your data when ready.</p>
    </section>

    <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <header class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Meals</h2>
            <button 
            wire:click='estimate'
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full">
                Enter
              </button>
        </header>
        <div class="flex h-32 items-center justify-center rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 text-gray-500">
            <textarea
            class="mt-1 block w-full h-full rounded-md pl-3 pt-1 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            rows="3"
            wire:model='food'
            placeholder="Write a summary of what you ate/drank..."
            ></textarea>
        </div>
        <div class="flex justify-end mt-4">
            <button
            wire:click="save"
            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-full"
        >
            Save
        </button>
        </div>
    </section>
</div>
