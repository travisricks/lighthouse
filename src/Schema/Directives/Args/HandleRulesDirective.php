<?php

namespace Nuwave\Lighthouse\Schema\Directives\Args;

use Nuwave\Lighthouse\Support\NoValue;
use Illuminate\Contracts\Validation\Validator;
use Nuwave\Lighthouse\Support\Traits\HasArgumentPath;

trait HandleRulesDirective
{
    use HasArgumentPath;

    /**
     * @param mixed    $argumentValue
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handleArgument($argumentValue, \Closure $next)
    {
        $value = $next($argumentValue);

        $rules = $this->getRules();

        if ($argumentValue instanceof NoValue && ! \in_array('required', $rules, true)) {
            return $value;
        }

        $validator = $this->createValidator(
            $value instanceof NoValue
                ? null
                : $value,
            $rules
        );

        if (! $validator->fails()) {
            return $value;
        }

        $this->accumulateError($validator);

        return $value;
    }

    /**
     * @param mixed $value
     * @param array $rules
     *
     * @return \Illuminate\Contracts\Validation\Factory|Validator
     */
    protected function createValidator($value, array $rules)
    {
        $argumentName = $this->definitionNode->name->value;

        $validator = validator(
            [$argumentName => $value],
            [$argumentName => $rules],
            $this->getMessages()
        );

        $validator->setAttributeNames([
            $argumentName => $this->argumentPathAsDotNotation(),
        ]);

        return $validator;
    }

    /**
     * Accumulate the error to ErrorBuffer.
     *
     * @param Validator $validator
     */
    protected function accumulateError(Validator $validator)
    {
        $errorMessages = $validator->errors()
                                   ->get($this->definitionNode->name->value);

        foreach ($errorMessages as $errorMessage) {
            $this->errorBuffer()
                 ->push(
                     $errorMessage,
                     $this->argumentPathAsDotNotation()
                 );
        }
    }

    public function getRules(): array
    {
        $rules = $this->directiveArgValue('apply');

        return [$this->argumentPathAsDotNotation() => $rules];
    }

    public function getMessages(): array
    {
        return collect((array) $this->directiveArgValue('messages'))
            ->mapWithKeys(function ($message, $rule) {
                $prefix = $this->argumentPathAsDotNotation();

                return ["{$prefix}.{$rule}" => $message];
            })
            ->all();
    }
}
