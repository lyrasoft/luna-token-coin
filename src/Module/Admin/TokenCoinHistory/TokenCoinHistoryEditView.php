<?php

declare(strict_types=1);

namespace Lyrasoft\TokenCoin\Module\Admin\TokenCoinHistory;

use Lyrasoft\TokenCoin\Module\Admin\TokenCoinHistory\Form\EditForm;
use Lyrasoft\TokenCoin\Entity\TokenCoinHistory;
use Lyrasoft\TokenCoin\Repository\TokenCoinHistoryRepository;
use Unicorn\View\FormAwareViewModelTrait;
use Unicorn\View\ORMAwareViewModelTrait;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Attributes\ViewMetadata;
use Windwalker\Core\Attributes\ViewModel;
use Windwalker\Core\Html\HtmlFrame;
use Windwalker\Core\Language\TranslatorTrait;
use Windwalker\Core\View\View;
use Windwalker\Core\View\ViewModelInterface;
use Windwalker\DI\Attributes\Autowire;

/**
 * The TokenCoinHistoryEditView class.
 */
#[ViewModel(
    layout: 'token-coin-history-edit',
    js: 'token-coin-history-edit.js'
)]
class TokenCoinHistoryEditView implements ViewModelInterface
{
    use TranslatorTrait;
    use ORMAwareViewModelTrait;
    use FormAwareViewModelTrait;

    public function __construct(
        #[Autowire] protected TokenCoinHistoryRepository $repository,
    ) {
    }

    /**
     * Prepare
     *
     * @param  AppContext  $app
     * @param  View        $view
     *
     * @return  mixed
     */
    public function prepare(AppContext $app, View $view): mixed
    {
        $id = $app->input('id');

        /** @var TokenCoinHistory $item */
        $item = $this->repository->getItem($id);

        // Bind item for injection
        $view[TokenCoinHistory::class] = $item;

        $form = $this->createForm(EditForm::class)
            ->fill(
                [
                    'item' => $this->repository->getState()->getAndForget('edit.data')
                        ?: $this->orm->extractEntity($item)
                ]
            );

        return compact('form', 'id', 'item');
    }

    #[ViewMetadata]
    protected function prepareMetadata(HtmlFrame $htmlFrame): void
    {
        $htmlFrame->setTitle(
            $this->trans('unicorn.title.edit', title: 'TokenCoinHistory')
        );
    }
}
