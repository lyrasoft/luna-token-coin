<?php

declare(strict_types=1);

namespace App\View;

/**
 * Global variables
 * --------------------------------------------------------------
 * @var $app       AppContext      Application context.
 * @var $vm        object          The view model object.
 * @var $uri       SystemUri       System Uri information.
 * @var $chronos   ChronosService  The chronos datetime service.
 * @var $nav       Navigator       Navigator object to build route.
 * @var $asset     AssetService    The Asset manage service.
 * @var $lang      LangService     The language translation service.
 */

use Lyrasoft\TokenCoin\Enum\TokenCoinAction;
use Unicorn\Field\ButtonRadioField;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Asset\AssetService;
use Windwalker\Core\DateTime\ChronosService;
use Windwalker\Core\Language\LangService;
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\Router\SystemUri;
use Windwalker\Form\Field\NumberField;
use Windwalker\Form\Field\TextareaField;

$id = $id ?: 'token-coin-changes-modal';

$actionsField = $app->make(ButtonRadioField::class)
    ->setName('action')
    ->setNamespace('token_coin')
    ->label('動作')
    ->defaultValue(TokenCoinAction::PLUS->value)
    ->option('增加', TokenCoinAction::PLUS->value)
    ->option('減少', TokenCoinAction::REDUCE->value, ['data-color-class' => 'btn-danger']);

$numberField = $app->make(NumberField::class)
    ->setName('value')
    ->setNamespace('token_coin')
    ->label('數值')
    ->defaultValue('0');

$noteField = $app->make(TextareaField::class)
    ->setName('note')
    ->setNamespace('token_coin')
    ->label('備註')
    ->rows(5);
?>
<div class="modal fade" id="{{ $id }}" tabindex="-1" role="dialog"
    aria-labelledby="{{ $id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="token-coin-changes-modal-label">
                    {{ $title ?: '變更點數' }}
                </h4>
                <button type="button" class="close btn-close" data-bs-dismiss="modal" data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true" class="visually-hidden">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <x-field :field="$actionsField" class="mb-4"></x-field>
                <x-field :field="$numberField" class="mb-4"></x-field>
                <x-field :field="$noteField" class="mb-4"></x-field>
                <div>
                    <button type="button"
                        class="btn btn-primary w-100"
                        onclick="u.grid('#admin-form').batch('token_coin_changes')"
                    >
                        送出
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
