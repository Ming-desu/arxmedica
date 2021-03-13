<?php

declare(strict_types=1);

namespace App\Core\Base;

use App\Core\ActionPayload;
use Directory;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Component\Config\Definition\ArrayNode;

abstract class BaseController
{
  /**
   * @param array|object|null $data
   * @return Response
   */
  protected function respondWithData(Response $response, $data = null, int $statusCode = 200): Response
  {
    $payload = new ActionPayload($statusCode, $data);

    return $this->respond($response, $payload);
  }

  /**
   * @param ActionPayload $payload
   * @return Response
   */
  protected function respond(Response $response, ActionPayload $payload): Response
  {
    $json = json_encode($payload, JSON_PRETTY_PRINT);
    $response->getBody()->write($json);

    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus($payload->getStatusCode());
  }

  /**
   * Handles all the files that were uploaded
   * 
   * @param UploadedFileInterface[] $files
   * @throws Exception
   * @return array|null
   */
  protected function handleUploadedFiles(array $uploadedFiles)
  {
    if (count($uploadedFiles) === 0) {
      return null;
    }

    $directory = __DIR__ . '/../../../public/uploads';

    $phpFileUploadErrors = array(
      0 => 'There is no error, the file uploaded with success',
      1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
      2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
      3 => 'The uploaded file was only partially uploaded',
      4 => 'No file was uploaded',
      6 => 'Missing a temporary folder',
      7 => 'Failed to write file to disk.',
      8 => 'A PHP extension stopped the file upload.',
    );

    $files = [];
    foreach ($uploadedFiles as $file) {
      // Single file support only
      if ($file instanceof UploadedFileInterface) {
        if ($file->getError() === UPLOAD_ERR_NO_FILE) {
          continue;
        }

        if ($file->getError() !== UPLOAD_ERR_OK) {
          throw new Exception($phpFileUploadErrors[$file->getError()], 400);
        }

        $filename = $this->moveUploadedFile($directory, $file);
        $files[] = [
          'file_name' => $filename
        ];
      }
    }

    return count($files) === 0 ? null : $files;
  }

  /**
   * Moves the uploaded file to the upload directory and assigns it a unique name
   * to avoid overwriting an existing uploaded file.
   *
   * @param string $directory The directory to which the file is moved
   * @param UploadedFileInterface $uploadedFile The file uploaded file to move
   *
   * @return string The filename of moved file
   */
  private function moveUploadedFile(string $directory, UploadedFileInterface $uploadedFile): string
  {
    // Create the directory if it does not exists
    if (!file_exists($directory)) {
      mkdir($directory);
    }

    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

    $basename = bin2hex(random_bytes(8));
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
  }
}
