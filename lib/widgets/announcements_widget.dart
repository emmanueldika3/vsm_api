import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:vsm_app/provider/announcement_provider.dart';

class AnnouncementsWidget extends StatefulWidget {
  const AnnouncementsWidget({super.key});

  // Couleurs Thème VSM
  static const Color greenPrimary = Color(0xFF1E5235);
  static const Color goldAccent = Color(0xFFD4AF37);
  static const Color darkBg = Color(0xFF0A1E13);
  static const Color cardBg = Color(0xFF122E1F);

  @override
  State<AnnouncementsWidget> createState() => _AnnouncementsWidgetState();
}

class _AnnouncementsWidgetState extends State<AnnouncementsWidget> {
  // Dialog pour Ajouter ou Modifier un communiqué
  void _showAnnouncementDialog(
    BuildContext context, {
    Announcement? announcement,
  }) {
    final isEditing = announcement != null;
    final titleController = TextEditingController(
      text: announcement?.title ?? '',
    );
    final contentController = TextEditingController(
      text: announcement?.content ?? '',
    );
    bool isImportant = announcement?.isUrgent ?? false;
    bool isSubmitting = false;

    showDialog(
      context: context,
      barrierDismissible: !isSubmitting,
      builder: (dialogContext) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              backgroundColor: AnnouncementsWidget.darkBg,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
                side: const BorderSide(
                  color: AnnouncementsWidget.goldAccent,
                  width: 0.8,
                ),
              ),
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: AnnouncementsWidget.greenPrimary,
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: AnnouncementsWidget.goldAccent,
                        width: 0.8,
                      ),
                    ),
                    child: Icon(
                      isEditing ? Icons.edit : Icons.campaign,
                      color: AnnouncementsWidget.goldAccent,
                      size: 18,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Text(
                    isEditing ? 'Modifier Communiqué' : 'Nouveau Communiqué',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    TextField(
                      controller: titleController,
                      enabled: !isSubmitting,
                      style: const TextStyle(color: Colors.white, fontSize: 13),
                      decoration: InputDecoration(
                        labelText: 'Titre du communiqué',
                        labelStyle: const TextStyle(
                          color: Colors.white70,
                          fontSize: 12,
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderSide: const BorderSide(color: Colors.white24),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderSide: const BorderSide(
                            color: AnnouncementsWidget.goldAccent,
                          ),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        filled: true,
                        fillColor: Colors.black26,
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: contentController,
                      enabled: !isSubmitting,
                      maxLines: 4,
                      style: const TextStyle(color: Colors.white, fontSize: 13),
                      decoration: InputDecoration(
                        labelText: 'Contenu du communiqué...',
                        labelStyle: const TextStyle(
                          color: Colors.white70,
                          fontSize: 12,
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderSide: const BorderSide(color: Colors.white24),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderSide: const BorderSide(
                            color: AnnouncementsWidget.goldAccent,
                          ),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        filled: true,
                        fillColor: Colors.black26,
                      ),
                    ),
                    const SizedBox(height: 12),
                    SwitchListTile(
                      title: const Text(
                        'Marquer comme URGENT',
                        style: TextStyle(
                          color: Colors.redAccent,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      value: isImportant,
                      activeColor: Colors.redAccent,
                      dense: true,
                      contentPadding: EdgeInsets.zero,
                      onChanged: isSubmitting
                          ? null
                          : (bool val) {
                              setDialogState(() {
                                isImportant = val;
                              });
                            },
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: isSubmitting
                      ? null
                      : () {
                          titleController.dispose();
                          contentController.dispose();
                          Navigator.of(dialogContext).pop();
                        },
                  child: const Text(
                    'Annuler',
                    style: TextStyle(color: Colors.white54),
                  ),
                ),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AnnouncementsWidget.goldAccent,
                    foregroundColor: Colors.black,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(6),
                    ),
                  ),
                  onPressed: isSubmitting
                      ? null
                      : () async {
                          final title = titleController.text.trim();
                          final content = contentController.text.trim();

                          if (title.isNotEmpty && content.isNotEmpty) {
                            setDialogState(() {
                              isSubmitting = true;
                            });

                            final provider = context
                                .read<AnnouncementProvider>();
                            final messenger = ScaffoldMessenger.of(context);
                            final navigator = Navigator.of(dialogContext);

                            try {
                              if (isEditing) {
                                await provider.updateAnnouncement(
                                  id: announcement.id,
                                  title: title,
                                  content: content,
                                  isUrgent: isImportant, // <-- FIX ICI
                                );
                              } else {
                                await provider.addAnnouncement(
                                  title: title,
                                  content: content,
                                  isUrgent: isImportant, // <-- FIX ICI
                                );
                              }

                              titleController.dispose();
                              contentController.dispose();
                              navigator.pop();

                              messenger.showSnackBar(
                                SnackBar(
                                  content: Text(
                                    isEditing
                                        ? 'Communiqué mis à jour !'
                                        : 'Communiqué publié avec succès !',
                                  ),
                                  backgroundColor:
                                      AnnouncementsWidget.greenPrimary,
                                ),
                              );
                            } catch (e) {
                              setDialogState(() {
                                isSubmitting = false;
                              });

                              messenger.showSnackBar(
                                SnackBar(
                                  content: Text(
                                    'Erreur lors de l\'enregistrement : $e',
                                  ),
                                  backgroundColor: Colors.redAccent,
                                ),
                              );
                            }
                          }
                        },
                  child: isSubmitting
                      ? const SizedBox(
                          height: 16,
                          width: 16,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.black,
                          ),
                        )
                      : Text(
                          isEditing ? 'Enregistrer' : 'Publier',
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                ),
              ],
            );
          },
        );
      },
    ).then((_) {
      // Libère la mémoire même si l'utilisateur clique en dehors de la boîte de dialogue
      titleController.dispose();
      contentController.dispose();
    });
  }

  // Confirmation de suppression
  void _confirmDelete(BuildContext context, String id) {
    showDialog(
      context: context,
      builder: (dialogContext) {
        bool isDeleting = false;

        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              backgroundColor: AnnouncementsWidget.darkBg,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
                side: const BorderSide(color: Colors.redAccent, width: 0.8),
              ),
              title: const Text(
                'Supprimer le communiqué',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              content: const Text(
                'Voulez-vous vraiment supprimer ce communiqué ? Cette action est irréversible.',
                style: TextStyle(color: Colors.white70, fontSize: 13),
              ),
              actions: [
                TextButton(
                  onPressed: isDeleting
                      ? null
                      : () => Navigator.of(dialogContext).pop(),
                  child: const Text(
                    'Annuler',
                    style: TextStyle(color: Colors.white54),
                  ),
                ),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.redAccent,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(6),
                    ),
                  ),
                  onPressed: isDeleting
                      ? null
                      : () async {
                          setDialogState(() {
                            isDeleting = true;
                          });

                          final provider = context.read<AnnouncementProvider>();
                          final messenger = ScaffoldMessenger.of(context);
                          final navigator = Navigator.of(dialogContext);

                          try {
                            await provider.deleteAnnouncement(id);
                            navigator.pop();

                            messenger.showSnackBar(
                              const SnackBar(
                                content: Text('Communiqué supprimé'),
                                backgroundColor: Colors.redAccent,
                              ),
                            );
                          } catch (e) {
                            setDialogState(() {
                              isDeleting = false;
                            });

                            messenger.showSnackBar(
                              SnackBar(
                                content: Text(
                                  'Erreur lors de la suppression : $e',
                                ),
                                backgroundColor: Colors.redAccent,
                              ),
                            );
                          }
                        },
                  child: isDeleting
                      ? const SizedBox(
                          height: 16,
                          width: 16,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text(
                          'Supprimer',
                          style: TextStyle(fontWeight: FontWeight.bold),
                        ),
                ),
              ],
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<AnnouncementProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading) {
          return const Center(
            child: Padding(
              padding: EdgeInsets.all(24.0),
              child: CircularProgressIndicator(
                color: AnnouncementsWidget.goldAccent,
              ),
            ),
          );
        }

        final announcements = provider.announcements;

        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: AnnouncementsWidget.cardBg,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: AnnouncementsWidget.goldAccent.withOpacity(0.3),
              width: 1,
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header avec Titre et Bouton Ajouter
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Row(
                    children: [
                      Icon(
                        Icons.campaign,
                        color: AnnouncementsWidget.goldAccent,
                        size: 20,
                      ),
                      SizedBox(width: 8),
                      Text(
                        'Communiqués Officiels',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  InkWell(
                    onTap: () => _showAnnouncementDialog(context),
                    borderRadius: BorderRadius.circular(6),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        color: AnnouncementsWidget.greenPrimary,
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(
                          color: AnnouncementsWidget.goldAccent,
                          width: 0.8,
                        ),
                      ),
                      child: const Row(
                        children: [
                          Icon(
                            Icons.add,
                            color: AnnouncementsWidget.goldAccent,
                            size: 16,
                          ),
                          SizedBox(width: 4),
                          Text(
                            'Publier',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // Liste des annonces
              if (announcements.isEmpty)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 20),
                  child: Center(
                    child: Text(
                      'Aucun communiqué pour le moment',
                      style: TextStyle(color: Colors.white38, fontSize: 13),
                    ),
                  ),
                )
              else
                ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: announcements.length,
                  separatorBuilder: (context, index) =>
                      const Divider(color: Colors.white10, height: 16),
                  itemBuilder: (context, index) {
                    final item = announcements[index];
                    return Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.black12,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: item.isUrgent
                              ? Colors.redAccent.withOpacity(0.5)
                              : Colors.white12,
                        ),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              if (item.isUrgent) ...[
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 6,
                                    vertical: 2,
                                  ),
                                  decoration: BoxDecoration(
                                    color: Colors.redAccent.withOpacity(0.2),
                                    borderRadius: BorderRadius.circular(4),
                                    border: Border.all(
                                      color: Colors.redAccent,
                                      width: 0.6,
                                    ),
                                  ),
                                  child: const Text(
                                    'URGENT',
                                    style: TextStyle(
                                      color: Colors.redAccent,
                                      fontSize: 9,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 8),
                              ],
                              Expanded(
                                child: Text(
                                  item.title,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 13,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                              // Boutons d'action (Modifier & Supprimer)
                              Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  InkWell(
                                    onTap: () => _showAnnouncementDialog(
                                      context,
                                      announcement: item,
                                    ),
                                    child: const Padding(
                                      padding: EdgeInsets.all(2.0),
                                      child: Icon(
                                        Icons.edit_outlined,
                                        color: AnnouncementsWidget.goldAccent,
                                        size: 18,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  InkWell(
                                    onTap: () =>
                                        _confirmDelete(context, item.id),
                                    child: const Padding(
                                      padding: EdgeInsets.all(2.0),
                                      child: Icon(
                                        Icons.delete_outline,
                                        color: Colors.redAccent,
                                        size: 18,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Text(
                            item.content,
                            style: const TextStyle(
                              color: Colors.white70,
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                ),
            ],
          ),
        );
      },
    );
  }
}
