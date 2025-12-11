import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../providers/booking_provider.dart';
import '../../widgets/booking/booking_tile.dart';
import '../../../data/models/booking.dart';

class BookingsPage extends ConsumerStatefulWidget {
  const BookingsPage({super.key});

  @override
  ConsumerState<BookingsPage> createState() => _BookingsPageState();
}

class _BookingsPageState extends ConsumerState<BookingsPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  String _selectedStatus = 'all';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _tabController.addListener(() {
      if (_tabController.indexIsChanging) {
        setState(() {
          _selectedStatus = _getStatusForTab(_tabController.index);
        });
      }
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  String _getStatusForTab(int index) {
    switch (index) {
      case 0:
        return 'all';
      case 1:
        return 'confirmed';
      case 2:
        return 'pending';
      case 3:
        return 'cancelled';
      default:
        return 'all';
    }
  }

  @override
  Widget build(BuildContext context) {
    final bookingsAsync = ref.watch(bookingListProvider(
      status: _selectedStatus == 'all' ? null : _selectedStatus,
    ));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Bookings'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.read(bookingListProvider().notifier).refresh(),
          ),
          PopupMenuButton<String>(
            icon: const Icon(Icons.filter_list),
            onSelected: (String value) {
              // Handle additional filters
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'today',
                child: Text('Today'),
              ),
              const PopupMenuItem(
                value: 'week',
                child: Text('This Week'),
              ),
              const PopupMenuItem(
                value: 'month',
                child: Text('This Month'),
              ),
            ],
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'All'),
            Tab(text: 'Confirmed'),
            Tab(text: 'Pending'),
            Tab(text: 'Cancelled'),
          ],
        ),
      ),
      body: bookingsAsync.when(
        data: (paginatedBookings) {
          final bookings = paginatedBookings.data;

          if (bookings.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.event_busy,
                    size: 64,
                    color: Theme.of(context).colorScheme.outline,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'No bookings found',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      color: Theme.of(context).colorScheme.outline,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    _selectedStatus == 'all'
                        ? 'You don\'t have any bookings yet'
                        : 'No ${_selectedStatus} bookings found',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: Theme.of(context).colorScheme.outline,
                    ),
                  ),
                ],
              ),
            );
          }

          return RefreshIndicator(
            onRefresh: () async {
              await ref.read(bookingListProvider().notifier).refresh();
            },
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: bookings.length,
              itemBuilder: (context, index) {
                final booking = bookings[index];
                return BookingTile(
                  booking: booking,
                  onTap: () => _showBookingDetails(context, booking),
                  onCancel: booking.isConfirmed
                      ? () => _showCancelConfirmation(context, booking)
                      : null,
                  onReschedule: booking.isConfirmed
                      ? () => _showRescheduleDialog(context, booking)
                      : null,
                );
              },
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.error_outline,
                size: 64,
                color: Theme.of(context).colorScheme.error,
              ),
              const SizedBox(height: 16),
              Text(
                'Error loading bookings',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 8),
              Text(
                error.toString(),
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: Theme.of(context).colorScheme.outline,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.refresh(bookingListProvider()),
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showBookingDetails(BuildContext context, Booking booking) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        maxChildSize: 0.9,
        minChildSize: 0.5,
        builder: (context, scrollController) => Container(
          padding: const EdgeInsets.all(20),
          child: ListView(
            controller: scrollController,
            children: [
              // Handle bar
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.outline,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // Title
              Text(
                'Booking Details',
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 20),

              // Booking info
              _buildDetailRow(context, 'Name', booking.name),
              _buildDetailRow(context, 'Email', booking.email),
              _buildDetailRow(
                context,
                'Date',
                DateFormat.yMMMEd().format(booking.startsAt),
              ),
              _buildDetailRow(
                context,
                'Time',
                '${DateFormat.jm().format(booking.startsAt)} - ${DateFormat.jm().format(booking.endsAt)}',
              ),
              _buildDetailRow(
                context,
                'Duration',
                '${booking.duration.inMinutes} minutes',
              ),
              _buildDetailRow(context, 'Status', booking.status.toUpperCase()),

              if (booking.bookingPage != null)
                _buildDetailRow(context, 'Booking Page', booking.bookingPage!.title),

              if (booking.notes?.isNotEmpty == true)
                _buildDetailRow(context, 'Notes', booking.notes!),

              if (booking.meetingLink != null) ...[
                const SizedBox(height: 16),
                ElevatedButton.icon(
                  onPressed: () {
                    // TODO: Open meeting link
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Opening meeting link...')),
                    );
                  },
                  icon: const Icon(Icons.video_call),
                  label: const Text('Join Meeting'),
                ),
              ],

              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDetailRow(BuildContext context, String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              label,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                fontWeight: FontWeight.w500,
                color: Theme.of(context).colorScheme.outline,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: Theme.of(context).textTheme.bodyMedium,
            ),
          ),
        ],
      ),
    );
  }

  void _showCancelConfirmation(BuildContext context, Booking booking) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cancel Booking'),
        content: Text(
          'Are you sure you want to cancel the booking for ${booking.name} on ${DateFormat.yMMMEd().add_jm().format(booking.startsAt)}?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Keep Booking'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.of(context).pop();
              try {
                await ref.read(bookingListProvider().notifier).cancelBooking(booking.id);
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Booking cancelled successfully')),
                  );
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Failed to cancel booking: $e')),
                  );
                }
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Theme.of(context).colorScheme.error,
              foregroundColor: Colors.white,
            ),
            child: const Text('Cancel Booking'),
          ),
        ],
      ),
    );
  }

  void _showRescheduleDialog(BuildContext context, Booking booking) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Reschedule feature coming soon')),
    );
  }
}