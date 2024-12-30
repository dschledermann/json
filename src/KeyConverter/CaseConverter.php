<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\KeyConverter;

use Attribute;
use Dschledermann\JsonCoder\CoderException;
use Jawira\CaseConverter\Convert;

/**
 * Delegate the key conversion to jawira/case-converter
 */
#[Attribute]
final class CaseConverter implements KeyConverterInterface
{
    public function __construct(
        private string $caseFormat
    ) {}

    public function getName(string $fieldName): string
    {
        $convert = new Convert($fieldName);
        return match ($this->caseFormat) {
            'Ada' => $convert->toAda(),
            'Camel' => $convert->toCamel(),
            'Cobol' => $convert->toCobol(),
            'Kebab' => $convert->toKebab(),
            'Macro' => $convert->toMacro(),
            'Pascal' => $convert->toPascal(),
            'Sentence' => $convert->toSentence(),
            'Snake' => $convert->toSnake(),
            'Title' => $convert->toTitle(),
            'Train' => $convert->toTrain(),
            default => throw new CoderException(sprintf(
                "[Thaqu4iet] Unknown format '%s'",
                $this->caseFormat,
            )),
        };
    }
}
