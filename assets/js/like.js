document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.like-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', async (e) => {
            const input = e.target;
            const card = input.closest('.track-card');
            const trackId = input.id.replace('like-', '');

            const trackName = card.querySelector('.track-title').textContent.trim();
            const artistName = card.querySelector('.track-artist').textContent.trim();
            const albumName = card.querySelector('.track-album').textContent.trim();
            const imageUrl = card.querySelector('img')?.src || null;

            try {
                const response = await fetch('/track/like', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest' // pas obligatoire mais utile
                    },
                    body: JSON.stringify({
                        trackId,
                        trackName,
                        artistName,
                        albumName,
                        imageUrl
                    })
                });

                const result = await response.json();

                if (result.status === 'liked') {
                    console.log(`❤️ ${trackName} ajouté`);
                } else if (result.status === 'unliked') {
                    console.log(`💔 ${trackName} retiré`);
                } else if (result.error) {
                    console.warn(result.error);
                }

            } catch (err) {
                console.error('Erreur:', err);
            }
        });
    });
});
