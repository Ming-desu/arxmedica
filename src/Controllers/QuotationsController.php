<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Core\Base\BaseController;
use App\Core\Validation;
use App\Repositories\QuotationRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class QuotationsController extends BaseController
{
  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * @var QuotationRepository
   */
  private $quotationRepository;

  /**
   * @var Messages
   */
  private $flash;

  /**
   * @var Twig
   */
  private $twig;

  /**
   * @var Validation
   */
  private $v;

  /**
   * QuotationsController Constructor
   * 
   * @param LoggerInterface $logger
   * @param QuotationRepository $quotationRepository
   * @param Messages $flash
   * @param Twig $twig
   */
  public function __construct(LoggerInterface $logger, QuotationRepository $quotationRepository, Messages $flash, Twig $twig)
  {
    $this->logger = $logger;
    $this->quotationRepository = $quotationRepository;
    $this->flash = $flash;
    $this->twig = $twig;
    $this->v = new Validation([], $this->getRules());
  }

  /**
   * Index View
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function index(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from QuotationsController::index started');
    $params = $request->getQueryParams();

    if (count($params) === 0)
      return $this->twig->render($response, 'pages/quotations/index.html.twig');

    $quotations = $this->quotationRepository->read($params);
    return $this->respondWithData($response, [
      'sub' => $quotations
    ]);
  }

  /**
   * Create View
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function create(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from QuotationsController::create started');
    return $this->twig->render($response, 'pages/quotations/create.html.twig');
  }

  /**
   * Store action, stores record in the database
   * 
   * @param Request $request
   * @param Response $response
   * @throws Exception
   * @return Response
   */
  public function store(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from QuotationsController::store started');

    // Sets data for the validation
    $this->v->setData($request->getParsedBody());

    if (!$this->v->runValidation())
      throw new Exception($this->v->getErrors()[0], 400);

    if (count($request->getParsedBody()['quotation_items']) === 0)
      throw new Exception('Quotation items cannot be empty.', 400);

    $quotation = $this->quotationRepository->create($this->v->getData());

    return $this->respondWithData($response, [
      'message' => 'Successfully created a quotation.',
      'sub' => $quotation
    ]);
  }

  /**
   * Edit View
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function edit(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from QuotationsController::edit started');

    $route = RouteContext::fromRequest($request);
    $id = $route->getRoute()->getArgument('id');

    $quotation = $this->quotationRepository->find(intval($id));

    if (!$quotation)
      return $this->twig->render($response, 'pages/404.html.twig');

    return $this->twig->render($response, 'pages/quotations/edit.html.twig', ['quotation' => $quotation->jsonSerialize()]);
  }

  /**
   * Update action, updates record in the database
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function update(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from QuotationsController::update');

    // Sets data for the validation
    $this->v->setData($request->getParsedBody());

    if (!$this->v->runValidation())
      throw new Exception($this->v->getErrors()[0], 400);

    if (count($request->getParsedBody()['quotation_items']) === 0)
      throw new Exception('Quotation items cannot be empty.', 400);

    $quotation = $this->quotationRepository->update($this->v->getData());

    return $this->respondWithData($response, [
      'message' => 'Successfully updated a quotation.',
      'sub' => $quotation
    ]);
  }

  /**
   * Delete action, delete record from the database
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function delete(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from QuotationsController::delete');

    $quotation = $this->quotationRepository->delete($request->getParsedBody());

    $this->flash->addMessage('toast', 'Successfully deleted a quotation.');

    return $this->respondWithData($response, [
      'message' => 'Successfully deleted a quotation.',
      'sub' => $quotation
    ]);
  }

  /**
   * Print View
   * 
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function print(Request $request, Response $response): Response
  {
    $this->logger->info('Dispatch from QuotationsController::print started');
    $route = RouteContext::fromRequest($request);
    $id = $route->getRoute()->getArgument('id');

    $quotation = $this->quotationRepository->print(intval($id), ['orderby' => 'c.name, p.brand, p.description']);

    if (!$quotation)
      return $this->twig->render($response, 'pages/404.html.twig');

    return $this->twig->render($response, 'pages/quotations/print.html.twig', ['quotation' => $quotation->jsonSerialize()]);
  }

  /**
   * The rules of the validation
   * 
   * @return array
   */
  private function getRules(): array
  {
    return array(
      'pr_no' => [
        'rules' => 'trim|required|special'
      ],
      'date_issued' => [
        'rules' => 'trim|required'
      ],
      'project_title' => [
        'rules' => 'trim|required|special'
      ],
      'project_description' => [
        'rules' => 'trim|required|special'
      ],
      'recipient_address_details' => [
        'rules' => 'trim|required|special'
      ],
      'recipient_municipality' => [
        'rules' => 'trim|required|alpha'
      ],
      'recipient_province' => [
        'rules' => 'trim|required|alpha'
      ],
      'main_recipient_first_name' => [
        'rules' => 'trim|required|alpha'
      ],
      'main_recipient_last_name' => [
        'rules' => 'trim|required|alpha'
      ],
      'main_recipient_position' => [
        'rules' => 'trim|required|alpha'
      ],
      'secondary_recipient_first_name' => [
        'rules' => 'optional|trim|alpha'
      ],
      'secondary_recipient_last_name' => [
        'rules' => 'optional|trim|alpha'
      ],
      'secondary_recipient_position' => [
        'rules' => 'optional|trim|alpha'
      ],
      'representative_first_name' => [
        'rules' => 'trim|required|alpha'
      ],
      'representative_last_name' => [
        'rules' => 'trim|required|alpha'
      ],
      'representative_contact_number' => [
        'rules' => 'optional|trim|integer'
      ]
    );
  }
}
