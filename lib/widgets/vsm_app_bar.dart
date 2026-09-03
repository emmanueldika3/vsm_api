import 'package:flutter/material.dart';

class VsmAppBar extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final String userRole; // Ex: 'ADMIN', 'COACH', 'TREASURER'
  final bool hasUnreadNotifications;
  final VoidCallback? onRefresh;
  final VoidCallback? onNotificationPressed;
  final VoidCallback? onSettingsPressed;
  final VoidCallback? onLogoutPressed;

  const VsmAppBar({
    super.key,
    this.title = 'VÉTÉRANS SANTÉ MAHÈN',
    this.userRole = 'ADMIN',
    this.hasUnreadNotifications = true,
    this.onRefresh,
    this.onNotificationPressed,
    this.onSettingsPressed,
    this.onLogoutPressed,
  });

  @override
  Size get preferredSize => const Size.fromHeight(65);

  Color _getBadgeColor(String role) {
    switch (role.toUpperCase()) {
      case 'ADMIN':
        return const Color(0xFF6B1D2F); // Bordeaux Red
      case 'COACH':
        return const Color(0xFF143824); // Dark Green
      case 'TREASURER':
        return const Color(0xFFB8860B); // Dark Goldenrod
      default:
        return Colors.blueGrey.shade800;
    }
  }

  @override
  Widget build(BuildContext context) {
    const Color greenPrimary = Color(0xFF1E5235);
    const Color goldAccent = Color(0xFFD4AF37);

    final String formattedRole = userRole.toUpperCase();

    return AppBar(
      elevation: 4,
      backgroundColor: greenPrimary,
      surfaceTintColor: Colors.transparent,
      automaticallyImplyLeading: false,
      titleSpacing: 12,
      title: Row(
        children: [
          // Logo Officiel avec Bordure Or
          Container(
            width: 52,
            height: 52,
            padding: const EdgeInsets.all(3),
            decoration: BoxDecoration(
              color: Colors.white,
              shape: BoxShape.circle,
              border: Border.all(color: goldAccent, width: 2),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.2),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Image.asset(
              'assets/images/logo_vsm.png',
              fit: BoxFit.contain,
              errorBuilder: (context, error, stackTrace) {
                return const Icon(
                  Icons.sports_soccer,
                  size: 26,
                  color: greenPrimary,
                );
              },
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                    letterSpacing: 0.3,
                  ),
                ),
                const SizedBox(height: 3),
                Row(
                  children: [
                    const Icon(Icons.location_on, color: goldAccent, size: 11),
                    const SizedBox(width: 2),
                    const Text(
                      'PK11',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        color: goldAccent,
                      ),
                    ),
                    const SizedBox(width: 6),
                    Container(
                      width: 3,
                      height: 3,
                      decoration: const BoxDecoration(
                        color: Colors.white54,
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 6),
                    // Badge dynamique d'Espace Réseau
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 6,
                        vertical: 1.5,
                      ),
                      decoration: BoxDecoration(
                        color: _getBadgeColor(formattedRole),
                        borderRadius: BorderRadius.circular(4),
                        border: Border.all(
                          color: goldAccent.withOpacity(0.6),
                          width: 0.5,
                        ),
                      ),
                      child: Text(
                        'ESPACE $formattedRole',
                        style: const TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w800,
                          color: Colors.white,
                          letterSpacing: 1,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
      actions: [
        // Action Rafraîchir
        IconButton(
          icon: const Icon(Icons.refresh, color: Colors.white, size: 22),
          tooltip: 'Rafraîchir',
          onPressed: onRefresh,
        ),
        // Action Notifications avec indicateur conditionnel
        Stack(
          alignment: Alignment.center,
          children: [
            IconButton(
              icon: const Icon(
                Icons.notifications_outlined,
                color: Colors.white,
                size: 22,
              ),
              tooltip: 'Notifications',
              onPressed: onNotificationPressed,
            ),
            if (hasUnreadNotifications)
              Positioned(
                top: 12,
                right: 12,
                child: Container(
                  width: 8,
                  height: 8,
                  decoration: const BoxDecoration(
                    color: goldAccent,
                    shape: BoxShape.circle,
                  ),
                ),
              ),
          ],
        ),
        // Menu d'options
        PopupMenuButton<String>(
          icon: const Icon(Icons.more_vert, color: Colors.white, size: 22),
          color: const Color(0xFF0A1E13),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
            side: const BorderSide(color: goldAccent, width: 0.5),
          ),
          onSelected: (value) {
            if (value == 'settings' && onSettingsPressed != null) {
              onSettingsPressed!();
            } else if (value == 'logout' && onLogoutPressed != null) {
              onLogoutPressed!();
            }
          },
          itemBuilder: (BuildContext context) => [
            const PopupMenuItem<String>(
              value: 'settings',
              child: Row(
                children: [
                  Icon(Icons.settings_outlined, color: Colors.white, size: 18),
                  SizedBox(width: 10),
                  Text(
                    'Paramètres',
                    style: TextStyle(color: Colors.white, fontSize: 13),
                  ),
                ],
              ),
            ),
            const PopupMenuDivider(height: 1),
            const PopupMenuItem<String>(
              value: 'logout',
              child: Row(
                children: [
                  Icon(Icons.logout, color: Colors.redAccent, size: 18),
                  SizedBox(width: 10),
                  Text(
                    'Déconnexion',
                    style: TextStyle(color: Colors.redAccent, fontSize: 13),
                  ),
                ],
              ),
            ),
          ],
        ),
        const SizedBox(width: 4),
      ],
    );
  }
}
