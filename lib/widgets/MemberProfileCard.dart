// lib/widgets/MemberProfileCard.dart

import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:image_picker/image_picker.dart';
import 'package:vsm_app/provider/auth_provider.dart';

class MemberProfileCard extends StatefulWidget {
  final VoidCallback onViewFullProfile;
  final VoidCallback? onEditPhoto;

  const MemberProfileCard({
    super.key,
    required this.onViewFullProfile,
    this.onEditPhoto,
  });

  @override
  State<MemberProfileCard> createState() => _MemberProfileCardState();
}

class _MemberProfileCardState extends State<MemberProfileCard> {
  Uint8List? _pickedImageBytes;

  Future<void> _handlePhotoTap() async {
    try {
      final ImagePicker picker = ImagePicker();
      final XFile? pickedFile = await picker.pickImage(
        source: ImageSource.gallery,
        imageQuality: 80,
      );

      if (pickedFile != null) {
        final bytes = await pickedFile.readAsBytes();
        setState(() {
          _pickedImageBytes = bytes;
        });
      }
    } catch (e) {
      debugPrint("Erreur sélection image : $e");
    }

    if (widget.onEditPhoto != null) {
      widget.onEditPhoto!();
    }
  }

  // Extrait proprement une valeur quel que soit le format de 'user' (Map ou objet)
  String _extractData(dynamic user, List<String> keys, String defaultValue) {
    if (user == null) return defaultValue;

    // Si user est une Map (ex: JSON de l'API)
    if (user is Map) {
      for (final key in keys) {
        if (user.containsKey(key) &&
            user[key] != null &&
            user[key].toString().isNotEmpty) {
          return user[key].toString();
        }
      }
    } else {
      // Si user est une instance d'objet de classe
      try {
        final dynamic val = (user as dynamic).name ?? (user as dynamic).nom;
        if (val != null) return val.toString();
      } catch (_) {}
    }

    return defaultValue;
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final dynamic user = authProvider.user;

    // Lecture sécurisée sans crash 'NoSuchMethodError'
    final String memberName = _extractData(user, [
      'name',
      'nom',
      'username',
    ], "Emmanuel Dika");
    final String memberTitle = _extractData(user, [
      'title',
      'titre',
      'poste',
    ], "Président");
    final String memberRoleLabel = _extractData(user, [
      'role',
      'roleLabel',
      'statut',
    ], "Capitaine / Admin");
    final String? serverAvatarUrl = user is Map
        ? user['avatarUrl']?.toString()
        : null;

    const Color vsmDarkGreen = Color(0xFF1E5235);
    const Color vsmBadgeGreen = Color(0xFF006837);
    const Color vsmGold = Color(0xFFFFC107);
    const Color cardBackground = Color.fromARGB(255, 226, 247, 226);

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: cardBackground,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.withOpacity(0.1)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min, // Empêche l'erreur d'espace infini
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16.0, 16.0, 16.0, 12.0),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                // Avatar éditable avec badge jaune
                GestureDetector(
                  onTap: _handlePhotoTap,
                  child: Stack(
                    children: [
                      Container(
                        width: 70,
                        height: 70,
                        decoration: const BoxDecoration(
                          shape: BoxShape.circle,
                          color: vsmDarkGreen,
                        ),
                        child: ClipOval(
                          child: _pickedImageBytes != null
                              ? Image.memory(
                                  _pickedImageBytes!,
                                  fit: BoxFit.cover,
                                )
                              : (serverAvatarUrl != null &&
                                    serverAvatarUrl.isNotEmpty)
                              ? Image.network(
                                  serverAvatarUrl,
                                  fit: BoxFit.cover,
                                )
                              : const Icon(
                                  Icons.person,
                                  color: Colors.white,
                                  size: 40,
                                ),
                        ),
                      ),
                      Positioned(
                        bottom: 0,
                        right: 0,
                        child: Container(
                          padding: const EdgeInsets.all(5),
                          decoration: BoxDecoration(
                            color: vsmGold,
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white, width: 1.5),
                          ),
                          child: const Icon(
                            Icons.camera_alt,
                            size: 13,
                            color: vsmDarkGreen,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 16),

                // Textes dynamiques
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        memberName,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.bold,
                          color: vsmDarkGreen,
                        ),
                      ),
                      const SizedBox(height: 1),
                      Text(
                        memberTitle,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(fontSize: 13, color: Colors.grey[600]),
                      ),
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: vsmBadgeGreen,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          memberRoleLabel,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          Divider(color: Colors.grey.withOpacity(0.15), height: 1),

          // Bouton bas
          Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: widget.onViewFullProfile,
              borderRadius: const BorderRadius.only(
                bottomLeft: Radius.circular(16),
                bottomRight: Radius.circular(16),
              ),
              child: const Padding(
                padding: EdgeInsets.symmetric(vertical: 12.0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.remove_red_eye_outlined,
                      size: 18,
                      color: vsmBadgeGreen,
                    ),
                    SizedBox(width: 6),
                    Text(
                      'Voir le profil complet',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: vsmBadgeGreen,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
