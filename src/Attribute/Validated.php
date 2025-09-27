<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use JsonException;
use Raxos\Contract\Http\HttpRequestModelInterface;
use Raxos\Contract\Http\Validate\ValidatorExceptionInterface;
use Raxos\Contract\Router\{AttributeInterface, RuntimeExceptionInterface, ValueProviderInterface};
use Raxos\Http\HttpFile;
use Raxos\Http\Validate\HttpClassValidator;
use Raxos\Router\Definition\Injectable;
use Raxos\Router\Error\UnexpectedException;
use Raxos\Router\Request\Request;
use Raxos\Router\RouterUtil;
use function file_get_contents;
use function json_decode;
use function json_validate;
use const JSON_THROW_ON_ERROR;

/**
 * Class Validated
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.7.0
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class Validated implements AttributeInterface, ValueProviderInterface
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function getRegex(Injectable $injectable): string
    {
        return RouterUtil::convertPathParam($injectable->name, 'string', $injectable->defaultValue->defined);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function getValue(Request $request, Injectable $injectable): HttpRequestModelInterface
    {
        try {
            /** @var class-string<HttpRequestModelInterface> $model */
            $model = $injectable->types[0];
            $data = $this->getData($request);

            $validator = new HttpClassValidator($model);
            $validator->validate($data);

            return $validator->get();
        } catch (ValidatorExceptionInterface $err) {
            throw new UnexpectedException($err, __METHOD__);
        }
    }

    /**
     * Returns the data to validate.
     *
     * @param Request $request
     *
     * @return array
     * @throws RuntimeExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    private function getData(Request $request): array
    {
        try {
            $contentType = $request->contentType();

            if ($contentType === 'application/json') {
                return $request->json();
            }

            $data = $request->post->toArray();

            if (($dataFile = $request->files->get('data')) !== null && $dataFile[0] instanceof HttpFile && $dataFile[0]->contentType === 'application/json') {
                $data = file_get_contents($dataFile[0]->temporaryFile);

                if (!json_validate($data)) {
                    throw new UnexpectedException(new JsonException('Invalid JSON'), __METHOD__);
                }

                $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
                $request->files->unset('data');
            }

            /**
             * @var string $key
             * @var HttpFile[] $files
             */
            foreach ($request->files as $key => $files) {
                foreach ($files as $file) {
                    if (!$file->isValid) {
                        continue;
                    }

                    $data[$key] ??= [];
                    $data[$key][] = $file;
                }

                if (!isset($data[$key])) {
                    continue;
                }

                if (!isset($data[$key][1])) {
                    $data[$key] = $data[$key][0];
                }
            }

            return $data;
        } catch (JsonException $err) {
            throw new UnexpectedException($err, __METHOD__);
        }
    }

}
