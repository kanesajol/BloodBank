import { useCallback, useEffect, useState } from 'react';
import {
  Alert,
  ActivityIndicator,
  FlatList,
  Platform,
  Pressable,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { deleteStaff, fetchStaff } from '../api/client';
import { useAuth } from '../context/AuthContext';
import { colors } from '../theme/layout';

export default function StaffScreen({ navigation }) {
  const { user, signOut } = useAuth();
  const [staff, setStaff] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const staffData = await fetchStaff();
      setStaff(staffData.staff ?? []);
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  const performSignOut = async () => {
    try {
      await signOut();
    } catch (signOutError) {
      const message = `Logged out on this device, but server logout failed: ${signOutError.message}`;
      if (Platform.OS === 'web' && typeof globalThis.alert === 'function') {
        globalThis.alert(message);
      } else {
        Alert.alert('Logged out on this device', signOutError.message);
      }
    }
  };

  const requestDelete = (item) => {
    const message = `Are you sure you want to delete ${item.name}? This staff account cannot be restored.`;

    const remove = async () => {
      try {
        await deleteStaff(item.id);
        await load();
      } catch (e) {
        setError(e.message);
      }
    };

    if (Platform.OS === 'web') {
      if (typeof globalThis.confirm === 'function' && globalThis.confirm(`Delete staff account?\n\n${message}`)) {
        void remove();
      }

      return;
    }

    Alert.alert(
      'Delete staff account?',
      message,
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Delete', style: 'destructive', onPress: () => { void remove(); } },
      ],
    );
  };

  const requestSignOut = () => {
    const message = 'You will need to sign in again to access the mobile app.';

    if (Platform.OS === 'web') {
      if (typeof globalThis.confirm === 'function' && globalThis.confirm(`Log out of your account?\n\n${message}`)) {
        void performSignOut();
      }

      return;
    }

    Alert.alert(
      'Log out of your account?',
      message,
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Log Out', style: 'destructive', onPress: () => { void performSignOut(); } },
      ],
    );
  };

  const listHeader = (
    <View>
      <View style={styles.pageHeading}>
        <View style={styles.pageIcon}>
          <Text style={styles.pageIconText}>♙</Text>
        </View>
        <View style={styles.pageHeadingCopy}>
          <Text style={styles.pageTitle}>Staff Management</Text>
          <Text style={styles.pageSubtitle}>Manage staff accounts and activity</Text>
        </View>
      </View>

      <View style={styles.profileSection}>
        <Pressable
          style={styles.signedInCard}
          onPress={() => navigation.navigate('StaffProfile')}
          accessibilityRole="button"
          accessibilityLabel="Open my profile"
        >
          <View style={styles.signedInAvatar}>
            <Text style={styles.signedInAvatarText}>{user?.name?.trim().charAt(0) || 'S'}</Text>
          </View>
          <View style={styles.signedInCopy}>
            <Text style={styles.signedInLabel}>My profile · Signed in as</Text>
            <Text style={styles.signedInName} numberOfLines={1}>{user?.name || 'Staff member'}</Text>
            <Text style={styles.signedInRole}>{user?.role || 'Staff'}</Text>
          </View>
          <View style={styles.activeBadge}>
            <View style={styles.activeDot} />
            <Text style={styles.activeText}>Active</Text>
          </View>
          <Text style={styles.profileChevron}>›</Text>
        </Pressable>

        <Pressable
          style={styles.signOutButton}
          onPress={requestSignOut}
          accessibilityRole="button"
        >
          <Text style={styles.signOutIcon}>↪</Text>
          <Text style={styles.signOutText}>Log Out of Account</Text>
        </Pressable>
      </View>

      <Pressable
        style={styles.logButton}
        onPress={() => navigation.navigate('StaffLog')}
        accessibilityRole="button"
      >
        <Text style={styles.logButtonIcon}>▤</Text>
        <Text style={styles.logButtonText}>Staff activity log</Text>
        <Text style={styles.actionChevron}>›</Text>
      </Pressable>

      <View style={styles.sectionHeading}>
        <Text style={styles.sectionTitle}>Staff accounts</Text>
        <Text style={styles.staffCount}>{staff.length}</Text>
      </View>
      {loading ? <ActivityIndicator color={colors.primary} style={styles.loader} /> : null}
      {error ? <Text style={styles.error}>{error}</Text> : null}
    </View>
  );

  return (
    <View style={styles.root}>
      <FlatList
        data={staff}
        keyExtractor={(item) => String(item.id)}
        contentContainerStyle={staff.length === 0 ? styles.emptyList : styles.listContent}
        ListHeaderComponent={listHeader}
        ListEmptyComponent={!loading && !error ? (
          <View style={styles.emptyCard}>
            <Text style={styles.emptyIcon}>♙</Text>
            <Text style={styles.emptyTitle}>No staff accounts</Text>
            <Text style={styles.emptyText}>Add a staff account to get started.</Text>
          </View>
        ) : null}
        renderItem={({ item }) => (
          <View style={styles.staffCard}>
            <View style={styles.staffAvatar}>
              <Text style={styles.staffAvatarText}>{item.name?.trim().charAt(0) || 'S'}</Text>
            </View>
            <View style={styles.staffCopy}>
              <Text style={styles.name} numberOfLines={1}>{item.name}</Text>
              <Text style={styles.staffMeta} numberOfLines={1}>@{item.username} · {item.role}</Text>
              <View style={styles.staffFooter}>
                <View style={styles.staffStatus}>
                  <View style={[styles.statusDot, item.status?.toLowerCase() !== 'active' && styles.statusDotInactive]} />
                  <Text style={styles.statusText}>{item.status}</Text>
                </View>
                {item.contact ? <Text style={styles.contactText}>{item.contact}</Text> : null}
              </View>
            </View>
            {item.id !== user?.id ? (
              <Pressable
                style={styles.deleteButton}
                onPress={() => requestDelete(item)}
                accessibilityRole="button"
                accessibilityLabel={`Delete ${item.name}`}
              >
                <Text style={styles.deleteIcon}>×</Text>
              </Pressable>
            ) : (
              <View style={styles.youBadge}>
                <Text style={styles.youBadgeText}>You</Text>
              </View>
            )}
          </View>
        )}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff', paddingHorizontal: 16, paddingTop: 12, paddingBottom: 8 },
  listContent: { paddingBottom: 20 },
  emptyList: { flexGrow: 1, paddingBottom: 20 },
  pageHeading: { flexDirection: 'row', alignItems: 'center', gap: 11, marginBottom: 14 },
  pageIcon: { width: 42, height: 42, borderRadius: 14, backgroundColor: '#fff1f1', alignItems: 'center', justifyContent: 'center' },
  pageIconText: { color: colors.primary, fontSize: 22, fontWeight: '800' },
  pageHeadingCopy: { flex: 1, minWidth: 0 },
  pageTitle: { color: colors.primary, fontSize: 18, fontWeight: '800' },
  pageSubtitle: { color: colors.muted, fontSize: 12, marginTop: 3 },
  signedInCard: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    padding: 12,
    borderWidth: 1,
    borderColor: '#f1dede',
    borderTopLeftRadius: 14,
    borderTopRightRadius: 14,
    backgroundColor: '#fffafa',
  },
  profileSection: {
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#f1dede',
    borderRadius: 14,
    overflow: 'hidden',
    backgroundColor: '#fff',
  },
  signedInAvatar: { width: 40, height: 40, borderRadius: 14, alignItems: 'center', justifyContent: 'center', backgroundColor: '#fff1f1' },
  signedInAvatarText: { color: colors.primary, fontSize: 17, fontWeight: '800' },
  signedInCopy: { flex: 1, minWidth: 0 },
  signedInLabel: { color: colors.muted, fontSize: 10, fontWeight: '600' },
  signedInName: { color: colors.text, fontSize: 13, fontWeight: '800', marginTop: 2 },
  signedInRole: { color: colors.muted, fontSize: 11, marginTop: 2 },
  activeBadge: { flexDirection: 'row', alignItems: 'center', gap: 5, paddingHorizontal: 8, paddingVertical: 5, borderRadius: 12, backgroundColor: '#ecfdf3' },
  activeDot: { width: 6, height: 6, borderRadius: 3, backgroundColor: '#16a34a' },
  activeText: { color: '#15803d', fontSize: 10, fontWeight: '700' },
  profileChevron: { color: colors.primary, fontSize: 24, fontWeight: '500', marginLeft: -3 },
  signOutButton: {
    minHeight: 42,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 7,
    borderTopWidth: 1,
    borderTopColor: '#f1dede',
    backgroundColor: '#fffafa',
  },
  signOutIcon: { color: colors.danger, fontSize: 17, fontWeight: '800' },
  signOutText: { color: colors.danger, fontSize: 12, fontWeight: '700' },
  logButton: { minHeight: 42, flexDirection: 'row', alignItems: 'center', gap: 7, paddingHorizontal: 10, marginBottom: 14, borderWidth: 1, borderColor: colors.border, borderRadius: 10, backgroundColor: '#fff' },
  logButtonIcon: { color: colors.primary, fontSize: 17, fontWeight: '700' },
  logButtonText: { flex: 1, color: colors.text, fontSize: 11, fontWeight: '700' },
  actionChevron: { color: colors.muted, fontSize: 20 },
  sectionHeading: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 8 },
  sectionTitle: { color: colors.text, fontSize: 14, fontWeight: '800' },
  staffCount: { color: colors.primary, fontSize: 11, fontWeight: '800', paddingHorizontal: 8, paddingVertical: 3, borderRadius: 10, backgroundColor: '#fff1f1' },
  loader: { marginVertical: 10 },
  error: { color: colors.danger, marginBottom: 8, fontSize: 12 },
  staffCard: { minHeight: 76, flexDirection: 'row', alignItems: 'center', gap: 10, padding: 11, marginBottom: 8, borderWidth: 1, borderColor: '#edf1f5', borderRadius: 13, backgroundColor: '#fff', shadowColor: '#0f172a', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.035, shadowRadius: 5, elevation: 1 },
  staffAvatar: { width: 38, height: 38, borderRadius: 13, alignItems: 'center', justifyContent: 'center', backgroundColor: '#fff1f1' },
  staffAvatarText: { color: colors.primary, fontSize: 15, fontWeight: '800' },
  staffCopy: { flex: 1, minWidth: 0 },
  name: { color: colors.text, fontSize: 13, fontWeight: '800' },
  staffMeta: { color: colors.muted, fontSize: 10, marginTop: 3 },
  staffFooter: { flexDirection: 'row', alignItems: 'center', gap: 10, marginTop: 5 },
  staffStatus: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  statusDot: { width: 6, height: 6, borderRadius: 3, backgroundColor: '#16a34a' },
  statusDotInactive: { backgroundColor: '#94a3b8' },
  statusText: { color: colors.muted, fontSize: 10 },
  contactText: { color: colors.muted, fontSize: 10 },
  deleteButton: { width: 32, height: 32, alignItems: 'center', justifyContent: 'center', borderRadius: 9, backgroundColor: '#fff1f2' },
  deleteIcon: { color: colors.danger, fontSize: 22, lineHeight: 24 },
  youBadge: { paddingHorizontal: 8, paddingVertical: 4, borderRadius: 10, backgroundColor: '#f1f5f9' },
  youBadgeText: { color: colors.muted, fontSize: 10, fontWeight: '700' },
  emptyCard: { alignItems: 'center', justifyContent: 'center', padding: 22, marginTop: 4, borderWidth: 1, borderColor: colors.border, borderRadius: 13, backgroundColor: '#fafafa' },
  emptyIcon: { color: '#cbd5e1', fontSize: 27 },
  emptyTitle: { color: colors.text, fontSize: 13, fontWeight: '800', marginTop: 5 },
  emptyText: { color: colors.muted, fontSize: 11, marginTop: 3 },
});
