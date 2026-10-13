(function () {
    'use strict';

    function showLoading() {
        document.body.classList.add('is-loading');

        document.querySelectorAll('button[type="submit"]').forEach(function (button) {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
        });
    }

    function hideLoading() {
        document.body.classList.remove('is-loading');

        document.querySelectorAll('button[type="submit"]').forEach(function (button) {
            button.disabled = false;
            button.removeAttribute('aria-busy');
        });
    }

    function showError(message) {
        let error = document.querySelector('[data-async-error]');

        if (!error) {
            error = document.createElement('div');
            error.className = 'alert alert-danger';
            error.setAttribute('data-async-error', 'true');

            const content = document.querySelector('.app-content') || document.querySelector('.card-body');
            if (content) {
                content.prepend(error);
            }
        }

        error.textContent = message;
        error.hidden = false;
    }

    async function readResponse(response) {
        const contentType = response.headers.get('content-type') || '';

        if (contentType.includes('application/json')) {
            const payload = await response.json();

            if (!response.ok || payload.success === false) {
                throw new Error(
                    payload.error && payload.error.message
                        ? payload.error.message
                        : 'The request could not be completed.'
                );
            }

            return payload;
        }

        return {
            success: response.ok,
            html: await response.text()
        };
    }

    function replaceDocument(html) {
        document.open();
        document.write(html);
        document.close();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', async function (event) {
                if (form.hasAttribute('data-native-submit')) {
                    return;
                }

                event.preventDefault();
                showLoading();

                try {
                    const method = (form.method || 'POST').toUpperCase();
                    const url = new URL(form.action || window.location.href, window.location.href);
                    const options = {
                        method: method,
                        headers: {
                            Accept: 'application/json'
                        },
                        credentials: 'same-origin'
                    };

                    if (method === 'GET') {
                        new FormData(form).forEach(function (value, name) {
                            url.searchParams.append(name, value);
                        });
                    } else {
                        options.body = new FormData(form);
                    }

                    const response = await fetch(url.href, options);
                    const payload = await readResponse(response);

                    if (payload.data && payload.data.keep_pos_open) {
                        hideLoading();
                        document.dispatchEvent(new CustomEvent('pos:checkout-complete', {
                            detail: payload.data
                        }));
                        return;
                    }

                    if (payload.data && payload.data.redirect) {
                        window.location.assign(payload.data.redirect);
                        return;
                    }

                    hideLoading();
                } catch (error) {
                    hideLoading();
                    if (form.closest('.pos-screen')) {
                        document.dispatchEvent(new CustomEvent('pos:checkout-error', {
                            detail: { message: error.message }
                        }));
                    } else {
                        showError(error.message);
                    }
                }
            });
        });

        document.querySelectorAll('a[href]').forEach(function (link) {
            link.addEventListener('click', async function (event) {
                const url = new URL(link.href, window.location.href);

                if (
                    event.defaultPrevented ||
                    url.origin !== window.location.origin ||
                    link.target === '_blank' ||
                    link.hasAttribute('download') ||
                    url.hash
                ) {
                    return;
                }

                event.preventDefault();
                showLoading();

                try {
                    const response = await fetch(url.href, {
                        headers: {
                            Accept: 'text/html'
                        },
                        credentials: 'same-origin'
                    });
                    const payload = await readResponse(response);

                    if (!payload.html) {
                        throw new Error('The page could not be loaded.');
                    }

                    window.history.pushState({}, '', url.href);
                    replaceDocument(payload.html);
                } catch (error) {
                    hideLoading();
                    showError(error.message);
                }
            });
        });

        window.addEventListener('popstate', function () {
            window.location.reload();
        });
    });
}());
