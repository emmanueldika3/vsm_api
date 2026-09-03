import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:vsm_app/provider/admin_dashboard_provider.dart';
import 'package:vsm_app/widgets/announcements_widget.dart';

class AdminDashboardScreen extends StatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AdminDashboardProvider>().fetchDashboardData;
    });
  }

  @override
  Widget build(BuildContext context) {
    // Le contenu spécifique à l'admin (scrollable)
    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            "Tableau de bord Administrateur",
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1E5235),
            ),
          ),
          const SizedBox(height: 16),

          // Injecte ici tes widgets admin (statistiques, raccourcis, validations, etc.)
          const SizedBox(height: 20),

          // Widget d'Annonces autonomisé
          const AnnouncementsWidget(),

          const SizedBox(height: 16),
        ],
      ),
    );
  }
}
