<?php
class Zolago_Rma_Model_Rma_Status
{
	/**
	 * Oczekuje na zamówienie kuriera
	 */
	const STATUS_PENDING_COURIER = "pending_courier";
	/**
	 * Oczekuje na nadanie przesyłki
	 */
	const STATUS_PENDING_PICKUP = "pending_pickup";
	/**
	 * Oczekuje na przesyłkę
	 */
	const STATUS_PENDING_DELIVERY = "pending_delivery";
	/**
	 * Nowe
	 */
	const STATUS_PENDING = "pending";
	/**
	 * Otrzymana przesyłka
	 */
	const STATUS_SHIPMENT_RECIVED = "shipment_recived";
	/**
	 * W trakcie wyjaśniania
	 */
	const STATUS_PROCESSING = "processing";
	/**
	 * Potwierdzona realizacja
	 */
	const STATUS_ACCEPTED = "acctepted";
	/**
	 * Odrzucona realizacja
	 */
	const STATUS_REJECTED = "rejected";
	/**
	 * Zamknięte - zrealizowane
	 */
	const STATUS_CLOSED_ACCEPTED = "closed_accepted";
	/**
	 * Zamknięte – niezrealizowane
	 */
	const STATUS_CLOSED_REJECTED = "closed_rejected";
}