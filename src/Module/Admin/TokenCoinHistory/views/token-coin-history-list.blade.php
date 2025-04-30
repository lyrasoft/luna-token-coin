<?php

declare(strict_types=1);

namespace App\View;

/**
 * Global variables
 * --------------------------------------------------------------
 * @var  $app       AppContext      Application context.
 * @var  $vm        TokenCoinHistoryListView The view model object.
 * @var  $uri       SystemUri       System Uri information.
 * @var  $chronos   ChronosService  The chronos datetime service.
 * @var  $nav       Navigator       Navigator object to build route.
 * @var  $asset     AssetService    The Asset manage service.
 * @var  $lang      LangService     The language translation service.
 */

use Lyrasoft\Luna\Entity\User;
use Lyrasoft\TokenCoin\Entity\TokenCoinHistory;
use Lyrasoft\TokenCoin\Enum\TokenCoinAction;
use Lyrasoft\TokenCoin\Service\TokenCoinService;
use Unicorn\Workflow\BasicStateWorkflow;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Asset\AssetService;
use Windwalker\Core\DateTime\ChronosService;
use Windwalker\Core\Language\LangService;
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\Router\SystemUri;
use Lyrasoft\TokenCoin\Module\Admin\TokenCoinHistory\TokenCoinHistoryListView;

/**
 * @var $item TokenCoinHistory
 */

$workflow = $app->service(BasicStateWorkflow::class);

?>

@extends('admin.global.body-list')

@section('toolbar-buttons')
    @include('list-toolbar')
@stop

@section('content')
    <form id="admin-form" action="" x-data="{ grid: $store.grid }"
        x-ref="gridForm"
        data-ordering="{{ $ordering }}"
        method="post">

        <x-filter-bar :form="$form" :open="$showFilters"></x-filter-bar>

        {{-- RESPONSIVE TABLE DESC --}}
        <div class="d-block d-lg-none mb-3">
            @lang('unicorn.grid.responsive.table.desc')
        </div>

        <div class="grid-table table-responsive-lg">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    {{-- Toggle --}}
                    <th style="width: 1%">
                        <x-toggle-all></x-toggle-all>
                    </th>

                    {{-- Title --}}
                    <th class="text-nowrap">
                        <x-sort field="token_coin_history.user_id">
                            使用者
                        </x-sort>
                    </th>

                    <th>
                        動作
                    </th>

                    <th class="text-end">
                        變動
                    </th>

                    <th class="text-end">
                        剩餘
                    </th>

                    <th>
                        時間
                    </th>

                    <th>
                        操作人員
                    </th>

                    <th>
                        備註
                    </th>

                    {{-- Delete --}}
                    <th style="width: 1%" class="text-nowrap">
                        @lang('unicorn.field.delete')
                    </th>

                    {{-- ID --}}
                    <th style="width: 1%" class="text-nowrap text-end">
                        <x-sort field="token_coin_history.id">
                            @lang('unicorn.field.id')
                        </x-sort>
                    </th>
                </tr>
                </thead>

                <tbody>
                @forelse($items as $i => $item)
                    @php
                        $user = $vm->tryEntity(User::class, $item->user);
                        $agent = $vm->tryEntity(User::class, $item->agent);
                    @endphp
                    <tr>
                        {{-- Checkbox --}}
                        <td>
                            <x-row-checkbox :row="$i" :id="$item->getId()"></x-row-checkbox>
                        </td>

                        {{-- Title --}}
                        <td>
                            <div>
                                <a href="{{ $nav->to('user_edit')->id($item->getTargetId()) }}"
                                    target="_blank">
                                    {{ $user?->getName() }}
                                </a>
                            </div>
                        </td>

                        <td>
                            <span class="badge fs-6 px-2 py-1 bg-{{ $item->getAction()->getColor() }}">
                            {{ $item->getAction()->getTitle($lang) }}
                            </span>
                        </td>

                        <td class="text-end">
                            <div class="fs-5 text-{{ $item->getAction()->getColor() }}">
                                {{ TokenCoinService::numberFormat($item->getAction()->handleNumber($item->getValue())) }}
                            </div>
                        </td>

                        <td class="text-end">
                            <div class="fs-5">
                                {{ TokenCoinService::numberFormat($item->getRemain()) }}
                            </div>
                        </td>

                        <td>
                            {{ $chronos->toLocalFormat($item->getDate()) }}
                        </td>

                        <td>
                            @if ($agent)
                                <a href="{{ $nav->to('user_edit')->id($item->getAgentId()) }}"
                                    class="text-muted"
                                    target="_blank">
                                    {{ $agent?->getName() }}
                                </a>
                            @endif
                        </td>

                        <td>
                            {{ $item->getNote() }}
                        </td>

                        {{-- Delete --}}
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                @click="grid.deleteItem('{{ $item->getId() }}')"
                                data-dos
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>

                        {{-- ID --}}
                        <td class="text-end">
                            {{ $item->getId() }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="30">
                            <div class="c-grid-no-items text-center" style="padding: 125px 0;">
                                <h3 class="text-secondary">@lang('unicorn.grid.no.items')</h3>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <div>
                <x-pagination :pagination="$pagination"></x-pagination>
            </div>
        </div>

        <div class="d-none">
            <input name="_method" type="hidden" value="PUT" />
            <x-csrf></x-csrf>
        </div>

        <x-batch-modal :form="$form" namespace="batch"></x-batch-modal>
    </form>

@stop
