<?php

declare(strict_types=1);

namespace App\View;

/**
 * Global variables
 * --------------------------------------------------------------
 * @var  $app       AppContext      Application context.
 * @var  $view      TokenCoinHistoryEditView  The view modal object.
 * @var  $uri       SystemUri       System Uri information.
 * @var  $chronos   ChronosService  The chronos datetime service.
 * @var  $nav       Navigator       Navigator object to build route.
 * @var  $asset     AssetService    The Asset manage service.
 * @var  $lang      LangService     The language translation service.
 */

use Lyrasoft\TokenCoin\Module\Admin\TokenCoinHistory\TokenCoinHistoryEditView;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Asset\AssetService;
use Windwalker\Core\DateTime\ChronosService;
use Windwalker\Core\Language\LangService;
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\Router\SystemUri;

?>

<div x-title="toolbar" x-data="{ form: $store.form }" class="l-toolbar">
    {{-- Save --}}
    <button type="button" class="btn btn-success btn-sm uni-btn-save"
        data-task="save"
        @click="form.post();"
        style="width: 150px"
    >
        <span class="fa fa-save"></span>
        @lang('unicorn.toolbar.save')
    </button>

    {{-- Save2Close --}}
    <button type="button" class="btn btn-primary btn-sm uni-btn-save2close"
        data-task="save"
        @click="form.post(null, { task: 'save2close' });">
        <span class="fa fa-check"></span>
        @lang('unicorn.toolbar.save2close')
    </button>

    {{-- Cancel --}}
    <a class="btn btn-default btn-outline-secondary btn-sm uni-btn-cancel"
        href="{{ $nav->to('token_coin_history_list') }}">
        <span class="fa fa-times"></span>
        @lang('unicorn.toolbar.cancel')
    </a>
</div>
