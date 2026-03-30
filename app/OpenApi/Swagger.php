<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.1',
    title: 'Logistic API',
)]
#[OA\Server(
    url: '/',
    description: 'Local server',
)]
class Swagger
{
    #[OA\Get(
        path: '/',
        summary: 'Landing page',
        tags: ['System'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Application landing page'
            ),
        ]
    )]
    public function welcome(): void
    {
    }

    #[OA\Get(
        path: '/slots/availability',
        summary: 'Get available slots',
        tags: ['Slots'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of available slots',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/SlotResource'),
                    example: [
                        [
                            'slot_id' => 1,
                            'capacity' => 10,
                            'remaining' => 6,
                        ],
                        [
                            'slot_id' => 2,
                            'capacity' => 8,
                            'remaining' => 3,
                        ],
                    ]
                )
            ),
        ]
    )]
    public function slotsAvailability(): void
    {
    }

    #[OA\Post(
        path: '/slots/{id}/hold',
        summary: 'Hold a slot by id',
        tags: ['Holds'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Slot ID',
                schema: new OA\Schema(type: 'integer', minimum: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['UUID'],
                properties: [
                    new OA\Property(
                        property: 'UUID',
                        type: 'integer',
                        minimum: 0,
                        description: 'Unsigned integer UUID',
                        example: 123456
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Slot hold was created',
                content: new OA\JsonContent(ref: '#/components/schemas/HoldActionResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Slot not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 409,
                description: 'Slot has no remaining capacity',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function slotHold(): void
    {
    }

    #[OA\Get(
        path: '/holds/current',
        summary: 'Get current active holds',
        tags: ['Holds'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of current active holds',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/HoldResource')
                )
            ),
        ]
    )]
    public function holdsCurrent(): void
    {
    }

    #[OA\Post(
        path: '/holds/{id}/confirm',
        summary: 'Confirm an existing hold',
        tags: ['Holds'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Hold ID',
                schema: new OA\Schema(type: 'integer', minimum: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hold was confirmed',
                content: new OA\JsonContent(ref: '#/components/schemas/HoldActionResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Hold not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 409,
                description: 'Invalid hold state for confirmation',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function holdConfirm(): void
    {
    }

    #[OA\Delete(
        path: '/holds/{id}',
        summary: 'Cancel/Delete an existing hold',
        tags: ['Holds'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Hold ID',
                schema: new OA\Schema(type: 'integer', minimum: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hold was cancelled/deleted',
                content: new OA\JsonContent(ref: '#/components/schemas/HoldActionResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Hold not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function holdDestroy(): void
    {
    }

    #[OA\Schema(
        schema: 'SlotResource',
        type: 'object',
        required: ['slot_id', 'capacity', 'remaining'],
        properties: [
            new OA\Property(property: 'slot_id', type: 'integer', example: 1),
            new OA\Property(property: 'capacity', type: 'integer', example: 10),
            new OA\Property(property: 'remaining', type: 'integer', example: 6),
        ]
    )]
    public function slotResourceSchema(): void
    {
    }

    #[OA\Schema(
        schema: 'HoldActionResponse',
        type: 'object',
        properties: [
            new OA\Property(property: 'message', type: 'string', example: 'ok'),
            new OA\Property(property: 'id', type: 'integer', example: 42),
            new OA\Property(property: 'status', type: 'string', example: 'held'),
        ]
    )]
    public function holdActionResponseSchema(): void
    {
    }

    #[OA\Schema(
        schema: 'HoldResource',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 42),
            new OA\Property(property: 'to_slot', type: 'integer', example: 1),
            new OA\Property(property: 'UUID', type: 'integer', example: 123456),
            new OA\Property(property: 'status', type: 'string', example: 'held'),
            new OA\Property(property: 'at_end', type: 'string', format: 'date-time', example: '2026-03-30T18:00:00+00:00'),
        ]
    )]
    public function holdResourceSchema(): void
    {
    }

    #[OA\Schema(
        schema: 'ErrorResponse',
        type: 'object',
        properties: [
            new OA\Property(property: 'message', type: 'string', example: 'Not found'),
            new OA\Property(
                property: 'errors',
                type: 'object',
                nullable: true
            ),
        ]
    )]
    public function errorResponseSchema(): void
    {
    }
}
