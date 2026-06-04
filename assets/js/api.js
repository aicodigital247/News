/**
 * NeuralPress AI News Network - Core API Wrapper Client
 */

const NeuralPressAPI = {
    baseUrl: '/api',

    /**
     * Fetch standard post sequence
     * @param {Object} params - category, lang, status
     */
    async getPosts(params = {}) {
        const query = new URLSearchParams(params).toString();
        try {
            const response = await fetch(`${this.baseUrl}/posts.php?${query}`);
            if (!response.ok) throw new Error('Failed to retrieve news stream from network nodes.');
            return await response.json();
        } catch (error) {
            console.error('API_ERROR:', error);
            return [];
        }
    },

    /**
     * Fetch deep details of a single post by slug
     * @param {string} slug 
     */
    async getPostBySlug(slug) {
        try {
            const response = await fetch(`${this.baseUrl}/post.php?slug=${encodeURIComponent(slug)}`);
            if (!response.ok) throw new Error('Requested investigation retraction check active.');
            return await response.json();
        } catch (error) {
            console.error('API_ERROR:', error);
            return null;
        }
    },

    /**
     * Fetch trending rankings index
     */
    async getTrending() {
        try {
            const response = await fetch(`${this.baseUrl}/trending.php`);
            if (!response.ok) throw new Error('Failed to synchronize trending indices.');
            return await response.json();
        } catch (error) {
            console.error('API_ERROR:', error);
            return [];
        }
    },

    /**
     * Create article draft sequence
     * @param {Object} postData {title, content, category, author_id, summary}
     */
    async createPost(postData) {
        try {
            const response = await fetch(`${this.baseUrl}/create_post.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(postData)
            });
            return await response.json();
        } catch (error) {
            console.error('API_ERROR:', error);
            return { success: false, error: error.message };
        }
    },

    /**
     * Verify safety metrics via linguistic AI evaluation
     * @param {number} postId 
     */
    async verifyPost(postId) {
        try {
            const response = await fetch(`${this.baseUrl}/verify_post.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: postId })
            });
            return await response.json();
        } catch (error) {
            console.error('API_ERROR:', error);
            return { success: false, error: error.message };
        }
    },

    /**
     * Trigger auto-generation with topic parameters
     * @param {string} topic 
     * @param {string} category 
     */
    async generateAiPost(topic, category) {
        try {
            const response = await fetch(`${this.baseUrl}/ai_generate.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ topic, category })
            });
            return await response.json();
        } catch (error) {
            console.error('API_ERROR:', error);
            return { success: false, error: error.message };
        }
    }
};

window.NeuralPressAPI = NeuralPressAPI;
