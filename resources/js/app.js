

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const isStandaloneApp = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

if (isStandaloneApp && navigator.share) {
	document.addEventListener('click', async (event) => {
		const labelLink = event.target.closest('[data-share-label]');

		if (!labelLink) {
			return;
		}

		event.preventDefault();

		try {
			const response = await fetch(labelLink.href, { credentials: 'same-origin' });
			const pdf = await response.blob();
			const file = new File([pdf], labelLink.dataset.shareLabel, { type: 'application/pdf' });

			if (!response.ok || (navigator.canShare && !navigator.canShare({ files: [file] }))) {
				window.location.assign(labelLink.href);
				return;
			}

			await navigator.share({ files: [file] });
		} catch (error) {
			if (error.name !== 'AbortError') {
				window.location.assign(labelLink.href);
			}
		}
	});
}

if ('serviceWorker' in navigator) {
	window.addEventListener('load', () => {
		navigator.serviceWorker.getRegistrations()
			.then((registrations) => Promise.all(registrations.map((registration) => registration.unregister())))
			.then(() => {
				if ('caches' in window) {
					return caches.keys().then((keys) => Promise.all(keys.map((key) => caches.delete(key))));
				}

				return undefined;
			})
			.catch((error) => {
				console.warn('Service worker cleanup failed.', error);
			});
	});
}
