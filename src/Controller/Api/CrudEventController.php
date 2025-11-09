<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Controller\Api;

use Beefeater\CrudEventBundle\Event\CrudAfterEntityDelete;
use Beefeater\CrudEventBundle\Event\CrudAfterEntityPersist;
use Beefeater\CrudEventBundle\Event\CrudBeforeEntityDelete;
use Beefeater\CrudEventBundle\Event\CrudBeforeEntityPersist;
use Beefeater\CrudEventBundle\Event\CrudOperation;
use Beefeater\CrudEventBundle\Event\EntityBeforeDeserialize;
use Beefeater\CrudEventBundle\Event\FilterBuildEvent;
use Beefeater\CrudEventBundle\Event\ListSettings;
use Beefeater\CrudEventBundle\Exception\PayloadValidationException;
use Beefeater\CrudEventBundle\Exception\ResourceNotFoundException;
use Beefeater\CrudEventBundle\Model\Filter;
use Beefeater\CrudEventBundle\Model\Page;
use Beefeater\CrudEventBundle\Model\PaginatedResult;
use Beefeater\CrudEventBundle\Model\Sort;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CrudEventController extends AbstractController
{
    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $dispatcher;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        EventDispatcherInterface $dispatcher,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->dispatcher = $dispatcher;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }


    public function create(Request $request): JsonResponse
    {
        $entityClass = $this->getEntityClass($request);

        $entity = $this->fromJson($request, $entityClass, null, ['create']);

        $version = $request->attributes->get('_version');
        $resourceName = $request->attributes->get('_resource');

        $this->logger->info('Creating new entity', [
            'class' => $entityClass,
            'resource' => $resourceName,
            'version' => $version,
        ]);

        $this->dispatcher->dispatch(new CrudBeforeEntityPersist(
            $entity,
            CrudOperation::CREATE,
            [],
            $version
        ), $resourceName . '.create.before_persist');

        $this->dispatcher->dispatch(new CrudBeforeEntityPersist(
            $entity,
            CrudOperation::CREATE,
            [],
            $version
        ), 'crud_event.create.before_persist');

        $this->validate($this->validator, $entity, 'create');

        $this->saveEntity($entity);

        $this->logger->info('Entity created successfully', [
            'id' => $entity->getId(),
            'class' => $entityClass,
            'resource' => $resourceName,
            'version' => $version,
        ]);

        $this->dispatcher->dispatch(new CrudAfterEntityPersist(
            $entity,
            CrudOperation::CREATE,
            [],
            $version
        ), $resourceName . '.create.after_persist');

        $this->dispatcher->dispatch(new CrudAfterEntityPersist(
            $entity,
            CrudOperation::CREATE,
            [],
            $version
        ), 'crud_event.create.after_persist');

        return $this->json($entity, JsonResponse::HTTP_CREATED);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $entityClass = $this->getEntityClass($request);

        $entity = $this->findEntity($request, $id);

        $this->fromJson($request, $entityClass, $entity, ['update']);

        $version = $request->attributes->get('_version');
        $resourceName = $request->attributes->get('_resource');
        $params = ['id' => $id];

        $this->dispatcher->dispatch(new CrudBeforeEntityPersist(
            $entity,
            CrudOperation::UPDATE,
            $params,
            $version
        ), $resourceName . '.update.before_persist');

        $this->dispatcher->dispatch(new CrudBeforeEntityPersist(
            $entity,
            CrudOperation::UPDATE,
            $params,
            $version
        ), 'crud_event.update.before_persist');

        $this->validate($this->validator, $entity, 'update');

        $this->saveEntity($entity);

        $this->dispatcher->dispatch(new CrudAfterEntityPersist(
            $entity,
            CrudOperation::UPDATE,
            $params,
            $version
        ), $resourceName . '.update.after_persist');

        $this->dispatcher->dispatch(new CrudAfterEntityPersist(
            $entity,
            CrudOperation::UPDATE,
            $params,
            $version
        ), 'crud_event.update.after_persist');

        $this->logger->info('Entity updated successfully', [
            'id' => $id,
            'class' => $entityClass,
            'resource' => $resourceName,
            'version' => $version,
        ]);

        return $this->json($entity, JsonResponse::HTTP_OK);
    }

    public function patch(Request $request, string $id): JsonResponse
    {
        $entityClass = $this->getEntityClass($request);

        $entity = $this->findEntity($request, $id);

        $this->fromJson($request, $entityClass, $entity, ['patch']);

        $version = $request->attributes->get('_version');
        $resourceName = $request->attributes->get('_resource');
        $params = ['id' => $id];

        $this->dispatcher->dispatch(new CrudBeforeEntityPersist(
            $entity,
            CrudOperation::PATCH,
            $params,
            $version
        ), $resourceName . '.patch.before_persist');

        $this->dispatcher->dispatch(new CrudBeforeEntityPersist(
            $entity,
            CrudOperation::PATCH,
            $params,
            $version
        ), 'crud_event.patch.before_persist');

        $this->validate($this->validator, $entity, 'patch');

        $this->saveEntity($entity);

        $this->dispatcher->dispatch(new CrudAfterEntityPersist(
            $entity,
            CrudOperation::PATCH,
            $params,
            $version
        ), $resourceName . '.patch.after_persist');

        $this->dispatcher->dispatch(new CrudAfterEntityPersist(
            $entity,
            CrudOperation::PATCH,
            $params,
            $version
        ), 'crud_event.patch.after_persist');
        $this->logger->info('Entity patched successfully', [
            'id' => $id,
            'class' => $entityClass,
            'resource' => $resourceName,
            'version' => $version
        ]);

        return $this->json($entity, JsonResponse::HTTP_OK);
    }

    public function read(Request $request, string $id): JsonResponse
    {
        $entity = $this->findEntity($request, $id);
        $this->logger->info('Entity read', ['id' => $id, 'class' => get_class($entity)]);
        return $this->json($entity, JsonResponse::HTTP_OK);
    }

    public function delete(Request $request, string $id): JsonResponse
    {
        $entity = $this->findEntity($request, $id);

        $params = ['id' => $id];
        $version = $request->attributes->get('_version');
        $resourceName = $request->attributes->get('_resource');

        $this->dispatcher->dispatch(new CrudBeforeEntityDelete(
            $entity,
            CrudOperation::DELETE,
            $params,
            $version
        ), $resourceName . '.delete.before_remove');

        $this->dispatcher->dispatch(new CrudBeforeEntityDelete(
            $entity,
            CrudOperation::DELETE,
            $params,
            $version
        ), 'crud_event.delete.before_remove');

        $entityClass = $this->getEntityClass($request);
        $tableName = $this->entityManager->getClassMetadata($entityClass)->getTableName();

        $this->entityManager->getConnection()->delete($tableName, ['id' => $id]);

        $this->dispatcher->dispatch(new CrudAfterEntityDelete(
            $entity,
            CrudOperation::DELETE,
            $params,
            $version
        ), $resourceName . '.delete.after_remove');

        $this->dispatcher->dispatch(new CrudAfterEntityDelete(
            $entity,
            CrudOperation::DELETE,
            $params,
            $version
        ), 'crud_event.delete.after_remove');

        $this->logger->info('Entity deleted', [
            'id' => $id,
            'class' => $entityClass,
            'resource' => $resourceName,
            'version' => $version,
        ]);

        return new JsonResponse(['message' => 'Entity deleted'], JsonResponse::HTTP_NO_CONTENT);
    }

    public function list(Request $request, Page $page, Sort $sort, Filter $filter): JsonResponse
    {
        $resourceName = $request->attributes->get('_resource');

        $this->logger->info('List requested', [
            'resource' => $resourceName,
            'page' => $page->getPage(),
            'pageSize' => $page->getPageSize(),
            'orderBy' => $sort->getOrderBy(),
            'criteria' => $filter->getCriteria(),
        ]);
        $this->dispatcher->dispatch(new ListSettings($request), $resourceName . '.list.list_settings');

        $this->dispatcher->dispatch(new FilterBuildEvent($request, $filter), 'crud_event.list.filter_build');

        $entityClass = $this->getEntityClass($request);
        $entityRepository = $this->entityManager->getRepository($entityClass);
        $orderBy = $sort->getOrderBy();
        $criteria = $filter->getCriteria() ?? [];

        $entities = $entityRepository->findPaginated($criteria, $orderBy, $page->getOffset(), $page->getLimit());
        $paginatedResponse = new PaginatedResult(
            $entities,
            $page->getPage(),
            $page->getPageSize(),
            $entityRepository->countByCriteria($criteria)
        );
        return $this->json($paginatedResponse, Response::HTTP_OK);
    }
    protected function fromJson(Request $request, $className, object $model = null, array $groups = []): object
    {
        $this->dispatcher->dispatch(new EntityBeforeDeserialize(
            $model,
            $className
        ), 'entity.before_deserialize');
        $context = ($model) ? [AbstractNormalizer::OBJECT_TO_POPULATE => $model] : [];

        if (!empty($groups)) {
            $context['groups'] = $groups;
        }

        return $this->serializer->deserialize($request->getContent(), $className, 'json', $context);
    }

    protected function validate(ValidatorInterface $validator, object $model, ?string $group = null): void
    {
        $groups = $group ? [$group] : null;
        $errors = $validator->validate($model, null, $groups);

        if (count($errors) > 0) {
            $this->logger->warning('Validation failed', [
                'model' => get_class($model),
                'errors' => (string) $errors,
            ]);
            throw new PayloadValidationException(get_class($model), $errors);
        }
    }

    private function saveEntity(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    private function getEntityClass(Request $request): ?string
    {
        $entityClass = $request->attributes->get('_entity');
        if (!$entityClass || !class_exists($entityClass)) {
            $this->logger->error('Invalid or missing "_entity" attribute', [
                '_entity' => $entityClass,
            ]);
            throw new BadRequestHttpException('Invalid or missing "_entity" attribute.');
        }

        return $entityClass;
    }

    private function findEntity(Request $request, string $id): ?object
    {
        $entityClass = $this->getEntityClass($request);

        $entity = $this->entityManager->getRepository($entityClass)->find($id);

        if ($entity === null) {
            $this->logger->warning('Entity not found', [
                'entityClass' => $entityClass,
                'id' => $id,
            ]);
            throw new ResourceNotFoundException($entityClass, $id);
        }

        return $entity;
    }
}
