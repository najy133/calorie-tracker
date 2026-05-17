{{--
    <x-meal-breakdown/>
    -------------------------------------------------------------
    Animated "Type a meal → watch it become numbers" section.

    Self-contained. Drop this file into:
        resources/views/components/meal-breakdown.blade.php

    Then use anywhere as:
        <x-meal-breakdown/>

    Requires:
      - Alpine.js (already installed)
      - Tailwind classes (already configured)
      - The four @keyframes / .bd-* rules from breakdown-keyframes.css
        appended to resources/css/app.css
--}}

<section
    class="bg-zinc-950 text-zinc-50 py-24 md:py-32 relative overflow-hidden"
    x-data="mealBreakdown()"
    x-init="play()"
    aria-label="Animated demo: how the AI breaks a sentence into calories"
>
    {{-- Soft background glow --}}
    <div
        class="absolute inset-0 pointer-events-none"
        style="background:
            radial-gradient(900px 600px at 70% 30%, rgb(16 185 129 / 0.10), transparent 60%),
            radial-gradient(700px 500px at 20% 80%, rgb(99 102 241 / 0.06), transparent 60%);"
        aria-hidden="true"
    ></div>

    <div class="max-w-5xl mx-auto px-6 md:px-10 relative">

        {{-- Heading --}}
        <header class="text-center mb-14">
            <p class="text-xs font-semibold text-emerald-400 uppercase tracking-widest">How it works</p>
            <h2 class="font-serif text-4xl md:text-5xl text-white mt-3 leading-tight tracking-tight">
                Type a meal.<br>Watch it become numbers.
            </h2>
        </header>

        {{-- Stage --}}
        <div
            class="max-w-2xl mx-auto transition-opacity duration-500"
            :class="fading ? 'opacity-0' : 'opacity-100'"
            aria-live="polite"
        >
            {{-- Typed input --}}
            <div class="rounded-xl border-2 border-dashed border-white/10 bg-white/[0.02] px-7 py-7 min-h-[96px] flex items-center font-serif text-2xl md:text-4xl leading-snug text-zinc-50">
                <span>
                    <template x-for="(seg, i) in segments()" :key="i">
                        <span>
                            <template x-if="!seg.ph">
                                <span x-text="seg.text"></span>
                            </template>
                            <template x-if="seg.ph">
                                <span
                                    class="bd-hl"
                                    :style="'--hl:' + macroColor(seg.ph)"
                                    x-text="seg.text"
                                ></span>
                            </template>
                        </span>
                    </template>
                    <span class="bd-caret"></span>
                </span>
            </div>

            {{-- AI pill --}}
            <div
                class="mt-5 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 text-sm font-medium transition-opacity duration-300"
                :class="showAi ? 'opacity-100' : 'opacity-0'"
            >
                <span class="bd-sparkle">✨</span>
                <span x-text="phase === 'estimating' ? 'Estimating…' : 'Estimated'"></span>
            </div>

            {{-- Phrase cards --}}
            <div class="mt-7 flex flex-wrap gap-3.5">
                <template
                    x-for="(ph, i) in (showCards ? meal.phrases.slice(0, revealed) : [])"
                    :key="mealIdx + '-' + i"
                >
                    <div
                        class="flex-1 min-w-[160px] bg-white/[0.04] border border-white/[0.08] rounded-xl px-4 py-3.5 bd-card"
                        :style="'border-top: 2px solid ' + macroColor(ph) + ';'"
                    >
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-xs font-medium text-zinc-400" x-text="ph.text"></span>
                            <span class="w-2 h-2 rounded-full" :style="'background:' + macroColor(ph)"></span>
                        </div>
                        <div class="font-mono text-2xl font-bold text-white leading-none mb-2 tabular-nums">
                            <span x-text="ph.kcal"></span><small class="font-sans text-[11px] font-medium text-zinc-500 ml-1">kcal</small>
                        </div>
                        <div class="flex gap-2.5 font-mono text-[11px] tabular-nums">
                            <template x-if="ph.p > 0"><span class="text-indigo-300" x-text="'P'+ph.p+'g'"></span></template>
                            <template x-if="ph.c > 0"><span class="text-amber-300" x-text="'C'+ph.c+'g'"></span></template>
                            <template x-if="ph.f > 0"><span class="text-rose-300" x-text="'F'+ph.f+'g'"></span></template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Total readout --}}
            <div
                class="mt-9 flex items-center gap-6 flex-wrap px-6 py-4 rounded-lg bg-emerald-500/[0.06] border border-emerald-500/20 transition-all duration-500"
                :class="showTotal ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'"
            >
                <div class="font-serif text-3xl text-emerald-400/60 leading-none">=</div>
                <div class="font-mono font-bold text-5xl text-white leading-none tracking-tight tabular-nums flex items-baseline gap-2">
                    <span x-text="meal.total.kcal.toLocaleString()"></span>
                    <small class="font-sans text-sm font-medium text-zinc-500">kcal</small>
                </div>
                <div class="ml-auto flex gap-4 font-mono text-sm font-semibold tabular-nums">
                    <span class="text-indigo-300" x-text="'P'+meal.total.p+'g'"></span>
                    <span class="text-amber-300" x-text="'C'+meal.total.c+'g'"></span>
                    <span class="text-rose-300" x-text="'F'+meal.total.f+'g'"></span>
                </div>
            </div>
        </div>

        <p class="text-center text-sm text-zinc-500 mt-16">
            No barcode, no database. Just a sentence and the answer.
        </p>
    </div>

    {{-- Alpine.js data + state machine. Inline to keep the component self-contained. --}}
    <script>
        function mealBreakdown() {
            return {
                /* ---- DATA — edit / extend this list to add more meals ----
                   start / len are 0-indexed character offsets into the sentence.
                   total.kcal should equal sum(phrases[].kcal). */
                meals: [
                    { sentence: '2 eggs, toast with butter',
                      phrases: [
                          { text: '2 eggs', start: 0,  len: 6, kcal: 140, p: 12, c: 1,  f: 10 },
                          { text: 'toast',  start: 8,  len: 5, kcal: 80,  p: 3,  c: 14, f: 1  },
                          { text: 'butter', start: 19, len: 6, kcal: 100, p: 0,  c: 0,  f: 11 },
                      ],
                      total: { kcal: 320, p: 15, c: 15, f: 22 } },

                    { sentence: 'grilled chicken & rice',
                      phrases: [
                          { text: 'grilled chicken', start: 0,  len: 15, kcal: 280, p: 50, c: 0,  f: 6 },
                          { text: 'rice',            start: 18, len: 4,  kcal: 300, p: 5,  c: 65, f: 1 },
                      ],
                      total: { kcal: 580, p: 55, c: 65, f: 7 } },

                    { sentence: 'iced latte with oat milk',
                      phrases: [
                          { text: 'iced latte', start: 0,  len: 10, kcal: 80, p: 4, c: 8,  f: 4 },
                          { text: 'oat milk',   start: 16, len: 8,  kcal: 60, p: 2, c: 10, f: 2 },
                      ],
                      total: { kcal: 140, p: 6, c: 18, f: 6 } },

                    { sentence: 'burrito bowl, double chicken',
                      phrases: [
                          { text: 'burrito bowl',   start: 0,  len: 12, kcal: 480, p: 22, c: 78, f: 12 },
                          { text: 'double chicken', start: 14, len: 14, kcal: 300, p: 56, c: 0,  f: 7  },
                      ],
                      total: { kcal: 780, p: 78, c: 78, f: 19 } },
                ],

                /* ---- STATE ---- */
                mealIdx:  0,
                phase:    'typing',   // typing | estimating | breakdown | total | fading | rest
                typedLen: 0,
                revealed: 0,

                /* ---- DERIVED ---- */
                get meal()       { return this.meals[this.mealIdx]; },
                get showAi()     { return ['estimating','breakdown','total'].includes(this.phase); },
                get showCards()  { return ['breakdown','total'].includes(this.phase); },
                get showTotal()  { return this.phase === 'total'; },
                get fading()     { return this.phase === 'fading' || this.phase === 'rest'; },

                /* ---- HELPERS ---- */
                sleep(ms) { return new Promise(r => setTimeout(r, ms)); },

                macroColor(ph) {
                    const max = Math.max(ph.p, ph.c, ph.f);
                    if (ph.p === max) return '#818cf8'; // indigo-400 (protein)
                    if (ph.c === max) return '#fbbf24'; // amber-400  (carbs)
                    return                  '#fb7185'; // rose-400   (fat)
                },

                /* Builds the typed sentence as an array of {text, ph?} segments
                   for x-for to render. Phrase segments get the .bd-hl underline. */
                segments() {
                    const sentence = this.meal.sentence;
                    const len      = this.typedLen;
                    const active   = this.meal.phrases
                        .slice(0, this.revealed)
                        .filter(ph => ph.start < len);
                    if (!active.length) return [{ text: sentence.slice(0, len), ph: null }];
                    const segs = [];
                    let i = 0;
                    for (const ph of active) {
                        if (ph.start > i) segs.push({ text: sentence.slice(i, ph.start), ph: null });
                        const endChar = Math.min(ph.start + ph.len, len);
                        segs.push({ text: sentence.slice(ph.start, endChar), ph });
                        i = endChar;
                    }
                    if (i < len) segs.push({ text: sentence.slice(i, len), ph: null });
                    return segs;
                },

                /* ---- MAIN LOOP ----
                   One async function drives the whole timeline. Easier to read
                   and tune than nested setTimeouts. */
                async play() {
                    while (true) {
                        // 1. typing
                        this.typedLen = 0; this.revealed = 0; this.phase = 'typing';
                        for (let i = 1; i <= this.meal.sentence.length; i++) {
                            this.typedLen = i;
                            await this.sleep(38);
                        }
                        await this.sleep(450);

                        // 2. AI thinking
                        this.phase = 'estimating';
                        await this.sleep(750);

                        // 3. reveal phrases
                        this.phase = 'breakdown';
                        for (let i = 1; i <= this.meal.phrases.length; i++) {
                            this.revealed = i;
                            await this.sleep(450);
                        }
                        await this.sleep(500);

                        // 4. show total, hold
                        this.phase = 'total';
                        await this.sleep(2400);

                        // 5. fade out, brief pause
                        this.phase = 'fading';
                        await this.sleep(650);
                        this.phase = 'rest';
                        await this.sleep(400);

                        // 6. next meal
                        this.mealIdx = (this.mealIdx + 1) % this.meals.length;
                    }
                },
            };
        }
    </script>
</section>
