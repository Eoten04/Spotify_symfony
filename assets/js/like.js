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
                const response = await fetch('/tracks/like', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
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

            } catch (err) {
                console.error('Erreur:', err);
                input.checked = !input.checked;
            }
        });
    });
});