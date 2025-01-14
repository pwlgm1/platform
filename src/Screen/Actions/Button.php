<?php

declare(strict_types=1);

namespace Orchid\Screen\Actions;

use Illuminate\Support\Str;
use Orchid\Platform\Http\Middleware\Turbo;
use Orchid\Screen\Action;

/**
 * Class Button.
 *
 * @method Button name(string $name = null)
 * @method Button modal(string $modalName = null)
 * @method Button icon(string $icon = null)
 * @method Button class(string $classes = null)
 * @method Button parameters(array|object $name)
 * @method Button confirm(string $confirm = true)
 * @method Button action(string $url)
 * @method Button disabled(bool $disabled = true)
 */
class Button extends Action
{
    /**
     * @var string
     */
    protected $view = 'platform::actions.button';

    /**
     * Default attributes value.
     *
     * @var array
     */
    protected $attributes = [
        'class'      => 'btn btn-link',
        'type'       => 'submit',
        'novalidate' => false,
        'method'     => null,
        'icon'       => null,
        'action'     => null,
        'confirm'    => null,
        'parameters' => [],
        'turbo'      => true,
        'form'       => 'post-form',
    ];

    /**
     * Attributes available for a particular tag.
     *
     * @var array
     */
    protected $inlineAttributes = [
        'form',
        'formaction',
        'formenctype',
        'formmethod',
        'formnovalidate',
        'formtarget',
        'type',
        'autofocus',
        'disabled',
        'tabindex',
    ];

    /**
     * Button constructor.
     */
    public function __construct()
    {
        $this->addBeforeRender(function () {
            if ($this->get('action') !== null) {
                return;
            }

            // correct URL for async request
            $url = Str::contains(request()->header('Accept', ''), Turbo::TURBO_STREAM_FORMAT)
                ? url()->previous()
                : url()->current();

            $query = http_build_query($this->get('parameters'));

            $action = rtrim("{$url}/{$this->get('method')}?{$query}", '/?');
            $this->set('action', $action);
        })->addBeforeRender(function () {
            $action = $this->get('action');

            if ($action !== null) {
                $this->set('formaction', $action);
            }
        });
    }

    /**
     * @return Button|\Orchid\Screen\Field
     */
    public function novalidate(bool $novalidate = true)
    {
        return $this->set('formnovalidate', var_export($novalidate, true));
    }

    /**
     * @return $this
     */
    public function method(string $name, array $parameters = []): self
    {
        return $this
            ->set('method', $name)
            ->when(! empty($parameters), function () use ($parameters) {
                $this->set('parameters', $parameters);
            });
    }

    /**
     * @param array|string $name
     * @param mixed        $parameters
     * @param bool         $absolute
     *
     * @return $this
     */
    public function route($name, $parameters = [], $absolute = true): self
    {
        return $this->action(route($name, $parameters, $absolute));
    }
}
