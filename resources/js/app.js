

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

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
