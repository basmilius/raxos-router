<?php
declare(strict_types=1);

namespace Raxos\OldRouter;

use Raxos\OldRouter\Effect\{NotFoundEffect, RedirectEffect, ResponseEffect, ResultEffect, VoidEffect};

/**
 * Interface RouterEffectHandlerInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter
 * @since 1.0.1
 */
interface RouterEffectHandlerInterface
{

    /**
     * Invoked when a not found effect is returned from router resolving.
     *
     * @param NotFoundEffect $effect
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function onNotFoundEffect(NotFoundEffect $effect): bool;

    /**
     * Invoked when a redirect effect is returned from router resolving.
     *
     * @param RedirectEffect $effect
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function onRedirectEffect(RedirectEffect $effect): bool;

    /**
     * Invoked when a response effect is returned from router resolving.
     *
     * @param ResponseEffect $effect
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function onResponseEffect(ResponseEffect $effect): bool;

    /**
     * Invoked when a result effect is returned from router resolving.
     *
     * @param ResultEffect $effect
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function onResultEffect(ResultEffect $effect): bool;

    /**
     * Invoked when a void effect is returned from router resolving.
     *
     * @param VoidEffect $effect
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.1
     */
    public function onVoidEffect(VoidEffect $effect): bool;

}
