import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../providers/booking_provider.dart';
import '../../../data/models/booking_page.dart';

class BookingForm extends ConsumerStatefulWidget {
  final BookingPage bookingPage;
  final DateTime selectedTimeSlot;
  final VoidCallback? onBookingCreated;
  final VoidCallback? onCancel;

  const BookingForm({
    super.key,
    required this.bookingPage,
    required this.selectedTimeSlot,
    this.onBookingCreated,
    this.onCancel,
  });

  @override
  ConsumerState<BookingForm> createState() => _BookingFormState();
}

class _BookingFormState extends ConsumerState<BookingForm> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _notesController = TextEditingController();

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final bookingCreation = ref.watch(bookingCreationProvider);
    final endTime = widget.selectedTimeSlot.add(Duration(minutes: widget.bookingPage.duration));

    return Card(
      margin: const EdgeInsets.all(16),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              // Header
              Row(
                children: [
                  Icon(
                    Icons.event_note,
                    color: Theme.of(context).colorScheme.primary,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Book: ${widget.bookingPage.title}',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  if (widget.onCancel != null)
                    IconButton(
                      onPressed: widget.onCancel,
                      icon: const Icon(Icons.close),
                    ),
                ],
              ),

              const SizedBox(height: 16),

              // Booking details
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.surfaceVariant.withOpacity(0.3),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: Theme.of(context).colorScheme.outline.withOpacity(0.2),
                  ),
                ),
                child: Column(
                  children: [
                    Row(
                      children: [
                        Icon(
                          Icons.access_time,
                          size: 16,
                          color: Theme.of(context).colorScheme.outline,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          '${widget.bookingPage.duration} minutes',
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: Theme.of(context).colorScheme.outline,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Icon(
                          Icons.calendar_today,
                          size: 16,
                          color: Theme.of(context).colorScheme.outline,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          DateFormat.yMMMEd().format(widget.selectedTimeSlot),
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: Theme.of(context).colorScheme.outline,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Icon(
                          Icons.schedule,
                          size: 16,
                          color: Theme.of(context).colorScheme.outline,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          '${DateFormat.jm().format(widget.selectedTimeSlot)} - ${DateFormat.jm().format(endTime)}',
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: Theme.of(context).colorScheme.outline,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              if (widget.bookingPage.description != null) ...[
                const SizedBox(height: 16),
                Text(
                  widget.bookingPage.description!,
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: Theme.of(context).colorScheme.outline,
                  ),
                ),
              ],

              const SizedBox(height: 24),

              // Form fields
              TextFormField(
                controller: _nameController,
                decoration: const InputDecoration(
                  labelText: 'Your Name',
                  prefixIcon: Icon(Icons.person),
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Name is required';
                  }
                  return null;
                },
                enabled: !bookingCreation.isLoading,
              ),

              const SizedBox(height: 16),

              TextFormField(
                controller: _emailController,
                decoration: const InputDecoration(
                  labelText: 'Email Address',
                  prefixIcon: Icon(Icons.email),
                  border: OutlineInputBorder(),
                ),
                keyboardType: TextInputType.emailAddress,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Email is required';
                  }
                  if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value)) {
                    return 'Please enter a valid email address';
                  }
                  return null;
                },
                enabled: !bookingCreation.isLoading,
              ),

              const SizedBox(height: 16),

              TextFormField(
                controller: _notesController,
                decoration: const InputDecoration(
                  labelText: 'Additional Notes (Optional)',
                  prefixIcon: Icon(Icons.note),
                  border: OutlineInputBorder(),
                  alignLabelWithHint: true,
                ),
                maxLines: 3,
                enabled: !bookingCreation.isLoading,
              ),

              const SizedBox(height: 24),

              // Error message
              if (bookingCreation.hasError) ...[
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.error.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: Theme.of(context).colorScheme.error.withOpacity(0.3),
                    ),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        Icons.error_outline,
                        color: Theme.of(context).colorScheme.error,
                        size: 20,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          'Failed to create booking: ${bookingCreation.error}',
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: Theme.of(context).colorScheme.error,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
              ],

              // Action buttons
              Row(
                children: [
                  if (widget.onCancel != null) ...[
                    Expanded(
                      child: OutlinedButton(
                        onPressed: bookingCreation.isLoading ? null : widget.onCancel,
                        child: const Text('Cancel'),
                      ),
                    ),
                    const SizedBox(width: 12),
                  ],
                  Expanded(
                    flex: 2,
                    child: ElevatedButton(
                      onPressed: bookingCreation.isLoading ? null : _submitBooking,
                      child: bookingCreation.isLoading
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Text('Confirm Booking'),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _submitBooking() async {
    if (!_formKey.currentState!.validate()) return;

    try {
      final endTime = widget.selectedTimeSlot.add(Duration(minutes: widget.bookingPage.duration));

      await ref.read(bookingCreationProvider.notifier).createBooking(
        bookingPageId: widget.bookingPage.id,
        name: _nameController.text.trim(),
        email: _emailController.text.trim(),
        startsAt: widget.selectedTimeSlot,
        endsAt: endTime,
        notes: _notesController.text.trim().isNotEmpty ? _notesController.text.trim() : null,
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Booking created successfully!'),
            backgroundColor: Colors.green,
          ),
        );
        widget.onBookingCreated?.call();
      }
    } catch (e) {
      // Error handling is done through the provider state
    }
  }
}