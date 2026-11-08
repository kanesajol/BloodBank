import { useState } from 'react';
import {
  Linking,
  Modal,
  Platform,
  Pressable,
  StatusBar,
  StyleSheet,
  Text,
  View,
} from 'react-native';

const PORTAL_URL = 'https://crh.doh.gov.ph/';

export default function SystemGuidelines({ withinSafeArea = false }) {
  const [open, setOpen] = useState(false);
  const top = withinSafeArea
    ? 36
    : (Platform.OS === 'android' ? (StatusBar.currentHeight || 0) + 8 : 52);

  return (
    <>
      <View style={[styles.actions, { top }]} pointerEvents="box-none">
        <Pressable
          accessibilityRole="button"
          accessibilityLabel="System Guidelines & Support"
          style={({ pressed }) => [styles.iconButton, pressed && styles.iconButtonPressed]}
          onPress={() => setOpen(true)}
        >
          <Text style={styles.icon}>?</Text>
        </Pressable>
        <Pressable
          accessibilityRole="link"
          accessibilityLabel="Official CRH website"
          style={({ pressed }) => [styles.iconButton, pressed && styles.iconButtonPressed]}
          onPress={() => Linking.openURL(PORTAL_URL)}
        >
          <Text style={styles.icon}>◎</Text>
        </Pressable>
      </View>

      <Modal
        visible={open}
        transparent
        animationType="fade"
        onRequestClose={() => setOpen(false)}
      >
        <Pressable style={styles.backdrop} onPress={() => setOpen(false)}>
          <Pressable style={styles.dialog} onPress={() => {}}>
            <Pressable
              accessibilityRole="button"
              accessibilityLabel="Close"
              style={styles.close}
              onPress={() => setOpen(false)}
            >
              <Text style={styles.closeText}>×</Text>
            </Pressable>
            <Text style={styles.title}>CRH Blood Bank — System Guidelines</Text>
            <Text style={styles.point}>
              <Text style={styles.pointLabel}>1. Authorized Access: </Text>
              This system is strictly for authorized CRH Blood Bank staff.
            </Text>
            <Text style={styles.point}>
              <Text style={styles.pointLabel}>2. Sign Up / Account Access: </Text>
              If you don't have an account yet, click 'Sign up' on the login screen or request access from the system administrator.
            </Text>
            <Text style={styles.point}>
              <Text style={styles.pointLabel}>3. Blood Bank Records: </Text>
              After you sign in, use Blood Collection to record donors and donations, Blood Consumption to record units given, and Reports to review those records. Enter each name, MBD or WI date, and address completely.
            </Text>
          </Pressable>
        </Pressable>
      </Modal>
    </>
  );
}

const styles = StyleSheet.create({
  actions: {
    position: 'absolute',
    right: 12,
    zIndex: 5,
    flexDirection: 'row',
    gap: 6,
  },
  iconButton: {
    width: 36,
    height: 36,
    borderRadius: 18,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#990000',
  },
  iconButtonPressed: {
    backgroundColor: 'rgba(153, 0, 0, 0.72)',
  },
  icon: {
    color: '#fff',
    fontSize: 18,
    fontWeight: '800',
    lineHeight: 20,
  },
  backdrop: {
    flex: 1,
    justifyContent: 'center',
    padding: 20,
    backgroundColor: 'rgba(15, 23, 42, 0.55)',
  },
  dialog: {
    borderRadius: 16,
    backgroundColor: '#fff',
    paddingHorizontal: 18,
    paddingTop: 18,
    paddingBottom: 16,
  },
  close: {
    position: 'absolute',
    top: 10,
    right: 10,
    width: 32,
    height: 32,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
  },
  closeText: {
    color: '#475569',
    fontSize: 24,
    lineHeight: 26,
  },
  title: {
    marginRight: 28,
    marginBottom: 14,
    color: '#7a0000',
    fontSize: 18,
    fontWeight: '800',
    lineHeight: 24,
  },
  point: {
    marginBottom: 12,
    color: '#1e293b',
    fontSize: 14,
    lineHeight: 21,
  },
  pointLabel: {
    fontWeight: '800',
  },
});
