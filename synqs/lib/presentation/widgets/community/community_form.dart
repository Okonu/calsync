import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../data/models/community.dart';

class CommunityForm extends ConsumerStatefulWidget {
  final Community? community;
  final VoidCallback? onSuccess;
  final VoidCallback? onCancel;

  const CommunityForm({
    super.key,
    this.community,
    this.onSuccess,
    this.onCancel,
  });

  @override
  ConsumerState<CommunityForm> createState() => _CommunityFormState();
}

class _CommunityFormState extends ConsumerState<CommunityForm> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _slugController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _websiteController = TextEditingController();
  final _contactEmailController = TextEditingController();
  final _timezoneController = TextEditingController();

  bool _isPublic = true;
  Color _selectedColor = const Color(0xFF3B82F6);
  bool _isLoading = false;

  final List<Color> _availableColors = [
    const Color(0xFF3B82F6), // Blue
    const Color(0xFF10B981), // Emerald
    const Color(0xFFF59E0B), // Amber
    const Color(0xFFEF4444), // Red
    const Color(0xFF8B5CF6), // Violet
    const Color(0xFFEC4899), // Pink
    const Color(0xFF06B6D4), // Cyan
    const Color(0xFF84CC16), // Lime
  ];

  @override
  void initState() {
    super.initState();
    if (widget.community != null) {
      _populateForm();
    }
    _nameController.addListener(_generateSlug);
  }

  void _populateForm() {
    final community = widget.community!;
    _nameController.text = community.name;
    _slugController.text = community.slug;
    _descriptionController.text = community.description ?? '';
    _websiteController.text = community.website ?? '';
    _contactEmailController.text = community.contactEmail ?? '';
    _timezoneController.text = community.timezone ?? '';
    _isPublic = community.isPublic;
    try {
      _selectedColor = Color(int.parse(community.color.replaceFirst('#', '0xFF')));
    } catch (e) {
      _selectedColor = const Color(0xFF3B82F6);
    }
  }

  void _generateSlug() {
    if (widget.community == null) { // Only auto-generate for new communities
      final name = _nameController.text.toLowerCase();
      final slug = name
          .replaceAll(RegExp(r'[^a-z0-9\s]'), '')
          .replaceAll(RegExp(r'\s+'), '-')
          .replaceAll(RegExp(r'-+'), '-')
          .replaceAll(RegExp(r'^-|-$'), '');

      if (slug != _slugController.text) {
        _slugController.text = slug;
      }
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _slugController.dispose();
    _descriptionController.dispose();
    _websiteController.dispose();
    _contactEmailController.dispose();
    _timezoneController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.community == null ? 'Create Community' : 'Edit Community'),
        actions: [
          if (widget.onCancel != null)
            TextButton(
              onPressed: _isLoading ? null : widget.onCancel,
              child: const Text('Cancel'),
            ),
        ],
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Basic Information
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Basic Information',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Community Name
                    TextFormField(
                      controller: _nameController,
                      decoration: const InputDecoration(
                        labelText: 'Community Name',
                        prefixIcon: Icon(Icons.group),
                        border: OutlineInputBorder(),
                        helperText: 'The public name of your community',
                      ),
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Community name is required';
                        }
                        if (value.trim().length < 3) {
                          return 'Community name must be at least 3 characters';
                        }
                        return null;
                      },
                      enabled: !_isLoading,
                    ),

                    const SizedBox(height: 16),

                    // Slug
                    TextFormField(
                      controller: _slugController,
                      decoration: InputDecoration(
                        labelText: 'URL Slug',
                        prefixIcon: const Icon(Icons.link),
                        border: const OutlineInputBorder(),
                        helperText: 'Used in the community URL: synqs.com/c/${_slugController.text}',
                        prefixText: '@',
                      ),
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'URL slug is required';
                        }
                        if (!RegExp(r'^[a-z0-9-]+$').hasMatch(value.trim())) {
                          return 'Slug can only contain lowercase letters, numbers, and hyphens';
                        }
                        if (value.trim().length < 3) {
                          return 'Slug must be at least 3 characters';
                        }
                        return null;
                      },
                      enabled: !_isLoading,
                    ),

                    const SizedBox(height: 16),

                    // Description
                    TextFormField(
                      controller: _descriptionController,
                      decoration: const InputDecoration(
                        labelText: 'Description',
                        prefixIcon: Icon(Icons.description),
                        border: OutlineInputBorder(),
                        helperText: 'Brief description of your community',
                        alignLabelWithHint: true,
                      ),
                      maxLines: 3,
                      enabled: !_isLoading,
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 16),

            // Appearance
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Appearance',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Color picker
                    Text(
                      'Brand Color',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: _availableColors.map((color) {
                        final isSelected = color.value == _selectedColor.value;
                        return GestureDetector(
                          onTap: _isLoading ? null : () {
                            setState(() {
                              _selectedColor = color;
                            });
                          },
                          child: Container(
                            width: 40,
                            height: 40,
                            decoration: BoxDecoration(
                              color: color,
                              shape: BoxShape.circle,
                              border: Border.all(
                                color: isSelected
                                    ? Theme.of(context).colorScheme.onSurface
                                    : Colors.transparent,
                                width: 3,
                              ),
                            ),
                            child: isSelected
                                ? const Icon(
                                    Icons.check,
                                    color: Colors.white,
                                    size: 20,
                                  )
                                : null,
                          ),
                        );
                      }).toList(),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 16),

            // Contact Information
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Contact Information',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Website
                    TextFormField(
                      controller: _websiteController,
                      decoration: const InputDecoration(
                        labelText: 'Website',
                        prefixIcon: Icon(Icons.language),
                        border: OutlineInputBorder(),
                        hintText: 'https://example.com',
                      ),
                      keyboardType: TextInputType.url,
                      validator: (value) {
                        if (value != null && value.isNotEmpty) {
                          if (!RegExp(r'^https?://').hasMatch(value)) {
                            return 'Website must start with http:// or https://';
                          }
                        }
                        return null;
                      },
                      enabled: !_isLoading,
                    ),

                    const SizedBox(height: 16),

                    // Contact Email
                    TextFormField(
                      controller: _contactEmailController,
                      decoration: const InputDecoration(
                        labelText: 'Contact Email',
                        prefixIcon: Icon(Icons.email),
                        border: OutlineInputBorder(),
                        hintText: 'contact@example.com',
                      ),
                      keyboardType: TextInputType.emailAddress,
                      validator: (value) {
                        if (value != null && value.isNotEmpty) {
                          if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value)) {
                            return 'Please enter a valid email address';
                          }
                        }
                        return null;
                      },
                      enabled: !_isLoading,
                    ),

                    const SizedBox(height: 16),

                    // Timezone
                    TextFormField(
                      controller: _timezoneController,
                      decoration: const InputDecoration(
                        labelText: 'Timezone',
                        prefixIcon: Icon(Icons.schedule),
                        border: OutlineInputBorder(),
                        hintText: 'America/New_York',
                      ),
                      enabled: !_isLoading,
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 16),

            // Settings
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Privacy Settings',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 8),

                    SwitchListTile(
                      title: const Text('Public Community'),
                      subtitle: const Text('Allow anyone to discover and view your community'),
                      value: _isPublic,
                      onChanged: _isLoading ? null : (value) {
                        setState(() {
                          _isPublic = value;
                        });
                      },
                      contentPadding: EdgeInsets.zero,
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 32),

            // Submit button
            ElevatedButton(
              onPressed: _isLoading ? null : _submitForm,
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 16),
              ),
              child: _isLoading
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : Text(widget.community == null ? 'Create Community' : 'Update Community'),
            ),

            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final colorHex = '#${_selectedColor.value.toRadixString(16).substring(2)}';

      final data = {
        'name': _nameController.text.trim(),
        'slug': _slugController.text.trim(),
        'description': _descriptionController.text.trim().isNotEmpty
            ? _descriptionController.text.trim()
            : null,
        'website': _websiteController.text.trim().isNotEmpty
            ? _websiteController.text.trim()
            : null,
        'contact_email': _contactEmailController.text.trim().isNotEmpty
            ? _contactEmailController.text.trim()
            : null,
        'timezone': _timezoneController.text.trim().isNotEmpty
            ? _timezoneController.text.trim()
            : null,
        'color': colorHex,
        'is_public': _isPublic,
      };

      if (widget.community == null) {
        // Create new community
        // await ref.read(communityCreationProvider.notifier).createCommunity(...);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Community created successfully!')),
        );
      } else {
        // Update existing community
        // await ref.read(communityListProvider.notifier).updateCommunity(widget.community!.id, data);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Community updated successfully!')),
        );
      }

      widget.onSuccess?.call();
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error: ${e.toString()}'),
          backgroundColor: Theme.of(context).colorScheme.error,
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }
}