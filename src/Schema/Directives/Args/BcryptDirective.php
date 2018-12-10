<?php

namespace Nuwave\Lighthouse\Schema\Directives\Args;

use Nuwave\Lighthouse\Support\Contracts\ArgTransformerDirective;

class BcryptDirective implements ArgTransformerDirective
{
    /**
     * Directive name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'bcrypt';
    }

    /**
     * Apply transformations on the ArgumentValue.
     *
     * @param string $argumentValue
     *
     * @return mixed
     */
    public function transform($argumentValue)
    {
        return \is_string($argumentValue) ?
                bcrypt($argumentValue) :
                $argumentValue;
    }
}
