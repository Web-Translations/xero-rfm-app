<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\RedirectResponse;
use Throwable;
use Webfox\Xero\Exceptions\OAuthException;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // You can add reportable callbacks here if needed
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // If user cancels Xero consent, the package throws OAuthException.
        if ($e instanceof OAuthException) {
            return $this->redirectToDashboardWithCancelMessage();
        }

        // Defensive: if callback query indicates access_denied, gracefully handle too
        if ($request->routeIs('xero.auth.callback') && $request->get('error') === 'access_denied') {
            return $this->redirectToDashboardWithCancelMessage();
        }

        return parent::render($request, $e);
    }

    private function redirectToDashboardWithCancelMessage(): RedirectResponse
    {
        return redirect()
            ->route('dashboard')
            ->withErrors('Xero connection was cancelled. You can connect your Xero account from the dashboard.');
    }
}


