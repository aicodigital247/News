/**
 * NeuralPress AI News Network - Core UI & Interaction Kit
 */

const NeuralPressUI = {
    init() {
        this.bindBreakingTicker();
        this.enhanceTables();
        this.setupDashboardTriggers();
    },

    /**
     * Pause animations or show interactive breaking updates
     */
    bindBreakingTicker() {
        const tickerContainer = document.querySelector('.animate-pulse');
        if (tickerContainer) {
            tickerContainer.addEventListener('mouseenter', () => {
                tickerContainer.style.animationPlayState = 'paused';
            });
            tickerContainer.addEventListener('mouseleave', () => {
                tickerContainer.style.animationPlayState = 'running';
            });
        }
    },

    /**
     * Add clean styling indicators
     */
    enhanceTables() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.classList.add('bg-slate-50/80');
            });
            row.addEventListener('mouseleave', () => {
                row.classList.remove('bg-slate-50/80');
            });
        });
    },

    /**
     * Interactively stream operations inside admin dashboard templates
     */
    setupDashboardTriggers() {
        const verifyForms = document.querySelectorAll('form[action="/api/verify_post.php"]');
        verifyForms.forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = form.querySelector('button');
                const postIdInput = form.querySelector('input[name="id"]');
                if (!btn || !postIdInput) return;

                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `<span class="inline-block animate-spin mr-1">↻</span> Evaluating...`;
                btn.classList.add('opacity-75');

                try {
                    const result = await window.NeuralPressAPI.verifyPost(postIdInput.value);
                    if (result.success) {
                        btn.className = "bg-emerald-600 text-white px-2.5 py-1 text-[10px] font-bold uppercase rounded-sm";
                        btn.innerHTML = `✓ Active Verified (${result.post.trust_score}%)`;
                        
                        // Close review row or adjust state styling if inside review queue list
                        const row = form.closest('tr');
                        if (row) {
                            setTimeout(() => {
                                row.style.transition = 'opacity 0.5s ease-out';
                                row.style.opacity = '0';
                                setTimeout(() => row.remove(), 500);
                            }, 1200);
                        }
                    } else {
                        btn.className = "bg-red-800 text-white px-2.5 py-1 text-[10px] font-bold uppercase rounded-sm";
                        btn.innerHTML = `⚠ Evaluation Failed`;
                        setTimeout(() => {
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                            btn.className = "bg-[#bb1919] text-white px-2.5 py-1 text-[10px] font-bold uppercase hover:bg-[#801111]";
                        }, 2500);
                    }
                } catch (err) {
                    console.error("Evaluation fault:", err);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            });
        });

        // AI generator deck inside administrative panels
        const topicTrigger = document.querySelector('#ai-generate-form');
        if (topicTrigger) {
            topicTrigger.addEventListener('submit', async (e) => {
                e.preventDefault();
                const topicInput = topicTrigger.querySelector('input[name="topic"]');
                const categorySelect = topicTrigger.querySelector('select[name="category"]');
                const submitBtn = topicTrigger.querySelector('button[type="submit"]');

                if (!topicInput || !submitBtn) return;

                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = `🧠 Launching Investigative Heuristics...`;
                submitBtn.classList.add('animate-pulse');

                try {
                    const response = await window.NeuralPressAPI.generateAiPost(topicInput.value, categorySelect ? categorySelect.value : 'Technology');
                    if (response.success) {
                        // Display clean success alert deck or relocate
                        const feedback = document.createElement('div');
                        feedback.className = "p-4 bg-emerald-950/25 border border-emerald-500/30 text-emerald-200 rounded text-xs leading-relaxed mt-4";
                        feedback.innerHTML = `
                            <strong>✓ Content Draft Generated Successfully!</strong><br>
                            Headline: "${response.title}"<br>
                            <a href="/news/${response.slug}" class="underline font-bold text-white hover:text-emerald-300">View Public Article →</a>
                        `;
                        topicTrigger.after(feedback);
                        topicInput.value = '';
                    } else {
                        alert("AI Stream Generation Timeout: " + (response.error || "Reason unverified."));
                    }
                } catch (err) {
                    console.error("AI Generation issue:", err);
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.classList.remove('animate-pulse');
                }
            });
        }
    }
};

window.NeuralPressUI = NeuralPressUI;
